<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireHipaaAcknowledgment
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

        // Skip HIPAA check for certain routes
        $excludedRoutes = [
            'hipaa.acknowledge',
            'hipaa.form',
            'logout',
            'auth.*',
        ];

        $currentRoute = $request->route()?->getName();
        
        foreach ($excludedRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return $next($request);
            }
        }

        // Check if user has acknowledged HIPAA
        if (!$user->hasAcknowledgedHipaa()) {
            // For API requests, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'HIPAA acknowledgment required',
                    'redirect_to' => route('hipaa.form'),
                ], 403);
            }

            // For web requests, redirect to HIPAA acknowledgment page
            return redirect()->route('hipaa.form')->with('message', 
                'You must acknowledge HIPAA compliance before accessing this system.'
            );
        }

        return $next($request);
    }
}