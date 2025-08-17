<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use OpenApi\Annotations as OA;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'two_factor_code' => ['nullable', 'string', 'size:6'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        // Check rate limiting
        $this->ensureIsNotRateLimited($request);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey($request));
            
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Check if user account is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact support.',
            ]);
        }

        // Check if user account is locked
        if ($user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'Your account is temporarily locked due to failed login attempts.',
            ]);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedLogins();
            RateLimiter::hit($this->throttleKey($request));
            
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Check two-factor authentication if enabled
        if ($user->hasTwoFactorEnabled()) {
            if (!$request->two_factor_code && !$request->recovery_code) {
                return redirect()->back()->withInput($request->only('email'))->withErrors([
                    'two_factor_code' => 'Two-factor authentication code is required.',
                ]);
            }

            if ($request->recovery_code) {
                if (!$user->useRecoveryCode($request->recovery_code)) {
                    $user->incrementFailedLogins();
                    throw ValidationException::withMessages([
                        'recovery_code' => 'Invalid recovery code.',
                    ]);
                }
            } else {
                $google2fa = app('pragmarx.google2fa');
                $secret = decrypt($user->two_factor_secret);
                
                if (!$google2fa->verifyKey($secret, $request->two_factor_code)) {
                    $user->incrementFailedLogins();
                    throw ValidationException::withMessages([
                        'two_factor_code' => 'Invalid two-factor authentication code.',
                    ]);
                }
            }
        }

        // Reset failed login attempts on successful login
        $user->resetFailedLogins();
        $user->updateLastLogin($request->ip());

        // Clear rate limiter
        RateLimiter::clear($this->throttleKey($request));

        // Log the login
        activity('user_login')
            ->causedBy($user)
            ->withProperties([
                'login_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'two_factor_used' => $user->hasTwoFactorEnabled(),
                'recovery_code_used' => !empty($request->recovery_code),
            ])
            ->log("User logged in: {$user->email}");

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect based on user type
        return $this->redirectBasedOnUserType($user);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login user",
     *     description="Authenticate user credentials and receive API token",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password", "device_name"},
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="SecurePassword123!"),
     *                 @OA\Property(property="device_name", type="string", maxLength=255, example="Mobile App"),
     *                 @OA\Property(property="two_factor_code", type="string", maxLength=6, example="123456", description="Required if 2FA is enabled"),
     *                 @OA\Property(property="recovery_code", type="string", example="recovery-code-123", description="Use instead of 2FA code")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Login successful"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="user_type", type="string", example="patient"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="two_factor_enabled", type="boolean", example=false),
     *                     @OA\Property(property="hipaa_acknowledged_at", type="string", format="date-time", nullable=true)
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123def456ghi789..."),
     *                 @OA\Property(property="abilities", type="array", @OA\Items(type="string"), example={"*"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="The provided credentials are incorrect.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or 2FA required",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Two-factor authentication code required"),
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="two_factor_code",
     *                         type="array",
     *                         @OA\Items(type="string", example="Two-factor authentication code is required")
     *                     )
     *                 ),
     *                 @OA\Property(property="requires_2fa", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many login attempts",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Too many login attempts. Please try again later.")
     *             )
     *         )
     *     )
     * )
     * Handle API login request.
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'two_factor_code' => ['nullable', 'string', 'size:6'],
            'recovery_code' => ['nullable', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        // Check rate limiting
        $this->ensureIsNotRateLimited($request);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey($request));
            
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check user status
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account deactivated',
            ], 403);
        }

        if ($user->isLocked()) {
            return response()->json([
                'message' => 'Account temporarily locked',
                'locked_until' => $user->locked_until,
            ], 423);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedLogins();
            RateLimiter::hit($this->throttleKey($request));
            
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check two-factor authentication
        if ($user->hasTwoFactorEnabled()) {
            if (!$request->two_factor_code && !$request->recovery_code) {
                return response()->json([
                    'message' => 'Two-factor authentication required',
                    'requires_2fa' => true,
                ], 202);
            }

            if ($request->recovery_code) {
                if (!$user->useRecoveryCode($request->recovery_code)) {
                    $user->incrementFailedLogins();
                    return response()->json([
                        'message' => 'Invalid recovery code',
                    ], 401);
                }
            } else {
                $google2fa = app('pragmarx.google2fa');
                $secret = decrypt($user->two_factor_secret);
                
                if (!$google2fa->verifyKey($secret, $request->two_factor_code)) {
                    $user->incrementFailedLogins();
                    return response()->json([
                        'message' => 'Invalid two-factor code',
                    ], 401);
                }
            }
        }

        // Reset failed attempts and update login info
        $user->resetFailedLogins();
        $user->updateLastLogin($request->ip());

        RateLimiter::clear($this->throttleKey($request));

        // Create API token
        $token = $user->createToken($request->device_name, 
            $this->getTokenAbilitiesForUserType($user->user_type)
        )->plainTextToken;

        // Log the API login
        activity('api_user_login')
            ->causedBy($user)
            ->withProperties([
                'login_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $request->device_name,
                'two_factor_used' => $user->hasTwoFactorEnabled(),
                'recovery_code_used' => !empty($request->recovery_code),
            ])
            ->log("User logged in via API: {$user->email}");

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
            ],
            'token' => $token,
            'requires_hipaa_acknowledgment' => !$user->hasAcknowledgedHipaa(),
            'has_valid_license' => $user->hasValidLicense(),
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log the logout
        if (Auth::check()) {
            activity('user_logout')
                ->causedBy(Auth::user())
                ->withProperties([
                    'logout_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("User logged out: " . Auth::user()->email);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'You have been logged out successfully.');
    }

    /**
     * Handle API logout request.
     */
    public function apiLogout(Request $request)
    {
        // Log the logout
        if (Auth::check()) {
            activity('api_user_logout')
                ->causedBy(Auth::user())
                ->withProperties([
                    'logout_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("User logged out via API: " . Auth::user()->email);
        }

        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Revoke all tokens for the authenticated user.
     */
    public function apiLogoutAll(Request $request)
    {
        // Log the logout all action
        if (Auth::check()) {
            activity('api_user_logout_all')
                ->causedBy(Auth::user())
                ->withProperties([
                    'logout_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("User logged out from all devices via API: " . Auth::user()->email);
        }

        // Revoke all tokens for the user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Redirect user based on their type after login.
     */
    private function redirectBasedOnUserType(User $user): RedirectResponse
    {
        // Temporarily redirect all users to the simple dashboard
        // until role-specific dashboards are properly set up
        return redirect()->intended(route('dashboard'));
        
        // Original code for future use:
        // $redirects = [
        //     'patient' => route('patient.dashboard'),
        //     'pharmacist' => route('pharmacist.dashboard'),
        //     'prescriber' => route('prescriber.dashboard'),
        //     'admin' => route('admin.dashboard'),
        // ];
        // $route = $redirects[$user->user_type] ?? route('dashboard');
        // return redirect()->intended($route);
    }

    /**
     * Get token abilities based on user type.
     */
    private function getTokenAbilitiesForUserType(string $userType): array
    {
        $abilities = [
            'patient' => [
                'user:read',
                'profile:read',
                'profile:update',
                'prescription:read',
                'order:create',
                'order:read',
            ],
            'pharmacist' => [
                'user:read',
                'profile:read',
                'profile:update',
                'prescription:read',
                'prescription:update',
                'prescription:dispense',
                'order:read',
                'order:update',
                'inventory:read',
                'inventory:update',
            ],
            'prescriber' => [
                'user:read',
                'profile:read',
                'profile:update',
                'prescription:create',
                'prescription:read',
                'prescription:update',
                'patient:read',
                'consultation:create',
            ],
            'admin' => [
                '*', // All abilities
            ],
        ];

        return $abilities[$userType] ?? ['user:read'];
    }
}