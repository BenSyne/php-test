<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

class HipaaAuditLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Capture request details for audit
        $auditData = [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'user_type' => auth()->user()?->user_type,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route_name' => $request->route()?->getName(),
            'request_id' => $request->header('X-Request-ID') ?: \Illuminate\Support\Str::uuid(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];

        // Handle sensitive data
        $sensitiveRoutes = [
            'profile.*',
            'patient.*',
            'medical.*',
            'prescription.*',
            'insurance.*',
            'payment.*',
        ];

        $isSensitiveRoute = collect($sensitiveRoutes)->some(function ($pattern) use ($request) {
            return fnmatch($pattern, $request->route()?->getName() ?? '');
        });

        if ($isSensitiveRoute) {
            $auditData['data_classification'] = 'PHI'; // Protected Health Information
            $auditData['requires_hipaa_compliance'] = true;
        }

        // Capture request body for certain operations (excluding passwords and sensitive data)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $requestData = $request->except([
                'password',
                'password_confirmation',
                'two_factor_code',
                'recovery_code',
                'ssn',
                'ssn_encrypted',
                'credit_card_number',
                'cvv',
            ]);

            // Hash sensitive data for audit trail
            if ($request->has(['ssn', 'credit_card_number'])) {
                $auditData['contains_sensitive_data'] = true;
                $auditData['sensitive_fields_hash'] = hash('sha256', 
                    implode('|', $request->only(['ssn', 'credit_card_number']))
                );
            }

            $auditData['request_data'] = $requestData;
        }

        // Process the request
        $response = $next($request);

        // Calculate response time
        $endTime = microtime(true);
        $auditData['response_time_ms'] = round(($endTime - $startTime) * 1000, 2);
        $auditData['response_status'] = $response->getStatusCode();

        // Determine access result
        $auditData['access_granted'] = $response->getStatusCode() < 400;
        $auditData['access_type'] = $this->determineAccessType($request);

        // Log for compliance - different log levels based on sensitivity
        if ($isSensitiveRoute) {
            // High-level HIPAA audit log
            Log::channel('hipaa')->info('PHI Access Attempt', $auditData);
            
            // Also log to activity log for database storage
            activity('hipaa_access')
                ->causedBy(auth()->user())
                ->withProperties($auditData)
                ->log("PHI access: {$request->method()} {$request->path()}");
        } else {
            // Standard audit log
            Log::channel('audit')->info('System Access', $auditData);
        }

        // Log failed access attempts with higher priority
        if (!$auditData['access_granted']) {
            Log::channel('security')->warning('Access Denied', array_merge($auditData, [
                'reason' => $this->getFailureReason($response->getStatusCode()),
            ]));
            
            // Track failed access attempts for security monitoring
            if (auth()->check()) {
                activity('security_event')
                    ->causedBy(auth()->user())
                    ->withProperties($auditData)
                    ->log("Access denied: {$response->getStatusCode()} for {$request->path()}");
            }
        }

        // Add audit headers to response for tracking
        $response->headers->set('X-Audit-ID', $auditData['request_id']);
        $response->headers->set('X-HIPAA-Compliant', $isSensitiveRoute ? 'true' : 'false');

        return $response;
    }

    /**
     * Determine the type of access being attempted.
     */
    private function determineAccessType(Request $request): string
    {
        $method = $request->method();
        $route = $request->route()?->getName() ?? $request->path();

        if (str_contains($route, 'login') || str_contains($route, 'auth')) {
            return 'authentication';
        }

        if (str_contains($route, 'profile') || str_contains($route, 'user')) {
            return 'user_data_access';
        }

        if (str_contains($route, 'patient') || str_contains($route, 'medical')) {
            return 'phi_access'; // Protected Health Information
        }

        if (str_contains($route, 'prescription') || str_contains($route, 'medication')) {
            return 'prescription_access';
        }

        if (str_contains($route, 'payment') || str_contains($route, 'billing')) {
            return 'financial_access';
        }

        switch ($method) {
            case 'GET':
                return 'data_read';
            case 'POST':
                return 'data_create';
            case 'PUT':
            case 'PATCH':
                return 'data_update';
            case 'DELETE':
                return 'data_delete';
            default:
                return 'system_access';
        }
    }

    /**
     * Get a human-readable failure reason based on status code.
     */
    private function getFailureReason(int $statusCode): string
    {
        return match ($statusCode) {
            401 => 'Authentication required',
            403 => 'Insufficient permissions',
            404 => 'Resource not found',
            422 => 'Validation failed',
            429 => 'Rate limit exceeded',
            500 => 'Internal server error',
            default => "HTTP {$statusCode}",
        };
    }
}