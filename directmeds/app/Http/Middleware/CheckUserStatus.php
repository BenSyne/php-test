<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user account is deactivated
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'account' => 'Your account has been deactivated. Please contact support.',
            ]);
        }

        // Check if user account is locked
        if ($user->isLocked()) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'account' => 'Your account is temporarily locked due to security reasons. Please try again later.',
            ]);
        }

        // Check if user has been soft deleted
        if ($user->trashed()) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'account' => 'Your account is no longer active. Please contact support.',
            ]);
        }

        return $next($request);
    }
}