<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use OpenApi\Annotations as OA;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $userTypes = [
            'patient' => 'Patient',
            'pharmacist' => 'Pharmacist',
            'prescriber' => 'Prescriber (Doctor/Nurse Practitioner)',
        ];

        return view('auth.register', compact('userTypes'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:patient,pharmacist,prescriber'],
            'phone' => ['nullable', 'string', 'regex:/^[\+]?[1-9][\d]{0,15}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            
            // Professional fields (required for healthcare providers)
            'license_number' => ['required_if:user_type,pharmacist,prescriber', 'string', 'max:255'],
            'license_state' => ['required_if:user_type,pharmacist,prescriber', 'string', 'size:2'],
            'license_expiry' => ['required_if:user_type,pharmacist,prescriber', 'date', 'after:today'],
            'dea_number' => ['nullable', 'string', 'max:255'],
            'npi_number' => ['nullable', 'string', 'max:255'],
            
            // Profile fields
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            
            // Agreements
            'terms_accepted' => ['accepted'],
            'privacy_accepted' => ['accepted'],
        ]);

        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'user_type' => $validatedData['user_type'],
            'phone' => $validatedData['phone'] ?? null,
            'date_of_birth' => $validatedData['date_of_birth'] ?? null,
            'license_number' => $validatedData['license_number'] ?? null,
            'license_state' => $validatedData['license_state'] ?? null,
            'license_expiry' => $validatedData['license_expiry'] ?? null,
            'dea_number' => $validatedData['dea_number'] ?? null,
            'npi_number' => $validatedData['npi_number'] ?? null,
            'is_active' => true,
        ]);

        // Create user profile
        UserProfile::create([
            'user_id' => $user->id,
            'first_name' => $validatedData['first_name'] ?? null,
            'last_name' => $validatedData['last_name'] ?? null,
            'address_line_1' => $validatedData['address_line_1'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state' => $validatedData['state'] ?? null,
            'postal_code' => $validatedData['postal_code'] ?? null,
            'country' => 'US',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
            'profile_visibility' => true,
        ]);

        // Assign appropriate role based on user type
        $this->assignUserRole($user, $validatedData['user_type']);

        // Log the registration
        activity('user_registration')
            ->causedBy($user)
            ->withProperties([
                'user_type' => $user->user_type,
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("User registered: {$user->email}");

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on user type
        return $this->redirectBasedOnUserType($user);
    }

    /**
     * Assign role to user based on user type.
     */
    private function assignUserRole(User $user, string $userType): void
    {
        // Ensure roles exist (they should be created by seeder)
        $roleMapping = [
            'patient' => 'patient',
            'pharmacist' => 'pharmacist',
            'prescriber' => 'prescriber',
        ];

        if (isset($roleMapping[$userType])) {
            $role = Role::firstOrCreate(['name' => $roleMapping[$userType]]);
            $user->assignRole($role);
        }
    }

    /**
     * Redirect user based on their type after registration.
     */
    private function redirectBasedOnUserType(User $user): RedirectResponse
    {
        $redirects = [
            'patient' => route('patient.dashboard'),
            'pharmacist' => route('pharmacist.dashboard'),
            'prescriber' => route('prescriber.dashboard'),
            'admin' => route('admin.dashboard'),
        ];

        $route = $redirects[$user->user_type] ?? route('dashboard');

        return redirect()->intended($route)->with('success', 
            'Registration successful! Welcome to Direct Meds.'
        );
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register new user",
     *     description="Create a new user account and receive an API token",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "email", "password", "password_confirmation", "user_type", "terms_accepted", "privacy_accepted"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255, example="john@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", minLength=8, example="SecurePassword123!"),
     *                 @OA\Property(property="password_confirmation", type="string", format="password", example="SecurePassword123!"),
     *                 @OA\Property(property="user_type", type="string", enum={"patient", "pharmacist", "prescriber"}, example="patient"),
     *                 @OA\Property(property="phone", type="string", pattern="^[\+]?[1-9][\d]{0,15}$", example="+1234567890"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-15"),
     *                 @OA\Property(property="license_number", type="string", maxLength=255, example="LIC123456"),
     *                 @OA\Property(property="license_state", type="string", maxLength=2, example="CA"),
     *                 @OA\Property(property="license_expiry", type="string", format="date", example="2025-12-31"),
     *                 @OA\Property(property="dea_number", type="string", maxLength=255, example="DEA123456"),
     *                 @OA\Property(property="npi_number", type="string", maxLength=255, example="1234567890"),
     *                 @OA\Property(property="first_name", type="string", maxLength=255, example="John"),
     *                 @OA\Property(property="last_name", type="string", maxLength=255, example="Doe"),
     *                 @OA\Property(property="address_line_1", type="string", maxLength=255, example="123 Main St"),
     *                 @OA\Property(property="city", type="string", maxLength=255, example="San Francisco"),
     *                 @OA\Property(property="state", type="string", maxLength=255, example="California"),
     *                 @OA\Property(property="postal_code", type="string", maxLength=10, example="94102"),
     *                 @OA\Property(property="terms_accepted", type="boolean", example=true),
     *                 @OA\Property(property="privacy_accepted", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="User registered successfully"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="user_type", type="string", example="patient"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123def456ghi789...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="email",
     *                         type="array",
     *                         @OA\Items(type="string", example="The email has already been taken.")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     * Handle JSON registration request for API.
     */
    public function apiStore(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:patient,pharmacist,prescriber'],
            'phone' => ['nullable', 'string', 'regex:/^[\+]?[1-9][\d]{0,15}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            
            // Professional fields
            'license_number' => ['required_if:user_type,pharmacist,prescriber', 'string', 'max:255'],
            'license_state' => ['required_if:user_type,pharmacist,prescriber', 'string', 'size:2'],
            'license_expiry' => ['required_if:user_type,pharmacist,prescriber', 'date', 'after:today'],
            'dea_number' => ['nullable', 'string', 'max:255'],
            'npi_number' => ['nullable', 'string', 'max:255'],
            
            // Profile fields
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            
            // Agreements
            'terms_accepted' => ['accepted'],
            'privacy_accepted' => ['accepted'],
        ]);

        // Create user and profile (same logic as web registration)
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'user_type' => $validatedData['user_type'],
            'phone' => $validatedData['phone'] ?? null,
            'date_of_birth' => $validatedData['date_of_birth'] ?? null,
            'license_number' => $validatedData['license_number'] ?? null,
            'license_state' => $validatedData['license_state'] ?? null,
            'license_expiry' => $validatedData['license_expiry'] ?? null,
            'dea_number' => $validatedData['dea_number'] ?? null,
            'npi_number' => $validatedData['npi_number'] ?? null,
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'first_name' => $validatedData['first_name'] ?? null,
            'last_name' => $validatedData['last_name'] ?? null,
            'address_line_1' => $validatedData['address_line_1'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state' => $validatedData['state'] ?? null,
            'postal_code' => $validatedData['postal_code'] ?? null,
            'country' => 'US',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'consent_to_email' => true,
            'profile_visibility' => true,
        ]);

        $this->assignUserRole($user, $validatedData['user_type']);

        event(new Registered($user));

        // Create API token for the user
        $token = $user->createToken('registration-token', 
            $this->getTokenAbilitiesForUserType($user->user_type)
        )->plainTextToken;

        // Log the registration
        activity('api_user_registration')
            ->causedBy($user)
            ->withProperties([
                'user_type' => $user->user_type,
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("User registered via API: {$user->email}");

        return response()->json([
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
            'requires_hipaa_acknowledgment' => !$user->hasAcknowledgedHipaa(),
        ], 201);
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