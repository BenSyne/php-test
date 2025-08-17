<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /**
     * Show the two-factor authentication setup form.
     */
    public function show(): View
    {
        $user = Auth::user();
        
        return view('auth.two-factor', [
            'user' => $user,
            'twoFactorEnabled' => $user->hasTwoFactorEnabled(),
            'recoveryCodes' => $user->two_factor_recovery_codes,
        ]);
    }

    /**
     * Enable two-factor authentication.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->back()->with('error', 'Two-factor authentication is already enabled.');
        }

        // Generate secret for the user
        $secret = $user->generateTwoFactorSecret();
        
        // Log the action
        activity('two_factor_setup_initiated')
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("Two-factor setup initiated for user: {$user->email}");

        return redirect()->back()->with([
            'success' => 'Two-factor authentication secret generated. Please scan the QR code and confirm with a code.',
            'qr_code' => $user->getTwoFactorQrCode(),
            'secret' => $secret,
        ]);
    }

    /**
     * Confirm two-factor authentication setup.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->back()->with('error', 'Two-factor authentication is already enabled.');
        }

        if (!$user->two_factor_secret) {
            return redirect()->back()->with('error', 'Please generate a two-factor secret first.');
        }

        if ($user->confirmTwoFactor($request->code)) {
            // Log successful setup
            activity('two_factor_enabled')
                ->causedBy($user)
                ->withProperties([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("Two-factor authentication enabled for user: {$user->email}");

            return redirect()->back()->with([
                'success' => 'Two-factor authentication has been enabled successfully.',
                'recovery_codes' => $user->fresh()->two_factor_recovery_codes,
            ]);
        }

        return redirect()->back()->withErrors([
            'code' => 'The provided code is invalid.',
        ]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->back()->with('error', 'Two-factor authentication is not enabled.');
        }

        $user->disableTwoFactor();

        // Log the action
        activity('two_factor_disabled')
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("Two-factor authentication disabled for user: {$user->email}");

        return redirect()->back()->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Generate new recovery codes.
     */
    public function generateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->back()->with('error', 'Two-factor authentication is not enabled.');
        }

        // Generate new recovery codes
        $user->update([
            'two_factor_recovery_codes' => $this->generateNewRecoveryCodes(),
        ]);

        // Log the action
        activity('two_factor_recovery_codes_regenerated')
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("Two-factor recovery codes regenerated for user: {$user->email}");

        return redirect()->back()->with([
            'success' => 'New recovery codes have been generated.',
            'recovery_codes' => $user->fresh()->two_factor_recovery_codes,
        ]);
    }

    /**
     * API: Enable two-factor authentication.
     */
    public function apiEnable(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled',
            ], 400);
        }

        $secret = $user->generateTwoFactorSecret();
        $qrCode = $user->getTwoFactorQrCode();
        
        // Log the action
        activity('two_factor_setup_initiated')
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'api_request' => true,
            ])
            ->log("Two-factor setup initiated via API for user: {$user->email}");

        return response()->json([
            'message' => 'Two-factor authentication secret generated',
            'secret' => $secret,
            'qr_code' => $qrCode,
            'instructions' => 'Scan the QR code with your authenticator app and confirm with a 6-digit code.',
        ]);
    }

    /**
     * API: Confirm two-factor authentication setup.
     */
    public function apiConfirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        
        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled',
            ], 400);
        }

        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => 'Please generate a two-factor secret first',
            ], 400);
        }

        if ($user->confirmTwoFactor($request->code)) {
            // Log successful setup
            activity('two_factor_enabled')
                ->causedBy($user)
                ->withProperties([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'api_request' => true,
                ])
                ->log("Two-factor authentication enabled via API for user: {$user->email}");

            return response()->json([
                'message' => 'Two-factor authentication enabled successfully',
                'recovery_codes' => $user->fresh()->two_factor_recovery_codes,
                'warning' => 'Save these recovery codes in a safe place. They can be used to access your account if you lose access to your authenticator app.',
            ]);
        }

        return response()->json([
            'message' => 'Invalid code provided',
        ], 422);
    }

    /**
     * API: Disable two-factor authentication.
     */
    public function apiDisable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled',
            ], 400);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid password',
            ], 422);
        }

        // Verify current 2FA code
        $google2fa = app('pragmarx.google2fa');
        $secret = decrypt($user->two_factor_secret);
        
        if (!$google2fa->verifyKey($secret, $request->code)) {
            return response()->json([
                'message' => 'Invalid two-factor code',
            ], 422);
        }

        $user->disableTwoFactor();

        // Log the action
        activity('two_factor_disabled')
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'api_request' => true,
            ])
            ->log("Two-factor authentication disabled via API for user: {$user->email}");

        return response()->json([
            'message' => 'Two-factor authentication has been disabled',
        ]);
    }

    /**
     * API: Generate new recovery codes.
     */
    public function apiGenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled',
            ], 400);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid password',
            ], 422);
        }

        // Verify current 2FA code
        $google2fa = app('pragmarx.google2fa');
        $secret = decrypt($user->two_factor_secret);
        
        if (!$google2fa->verifyKey($secret, $request->code)) {
            return response()->json([
                'message' => 'Invalid two-factor code',
            ], 422);
        }

        // Generate new recovery codes
        $recoveryCodes = $this->generateNewRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $recoveryCodes]);

        // Log the action
        activity('two_factor_recovery_codes_regenerated')
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'api_request' => true,
            ])
            ->log("Two-factor recovery codes regenerated via API for user: {$user->email}");

        return response()->json([
            'message' => 'New recovery codes generated',
            'recovery_codes' => $recoveryCodes,
            'warning' => 'Save these recovery codes in a safe place. Your old recovery codes are no longer valid.',
        ]);
    }

    /**
     * API: Get two-factor authentication status.
     */
    public function apiStatus(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at,
            'recovery_codes_count' => $user->two_factor_recovery_codes ? count($user->two_factor_recovery_codes) : 0,
            'has_backup_codes' => !empty($user->two_factor_recovery_codes),
        ]);
    }

    /**
     * Generate new recovery codes for two-factor authentication.
     */
    private function generateNewRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = str_replace('-', '', \Illuminate\Support\Str::uuid());
        }
        return $codes;
    }
}