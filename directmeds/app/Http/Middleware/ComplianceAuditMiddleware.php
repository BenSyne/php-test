<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ComplianceAuditMiddleware
{
    /**
     * Handle an incoming request and create comprehensive audit logs.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Capture pre-request context
        $requestContext = $this->captureRequestContext($request);
        
        // Process the request
        $response = $next($request);
        
        // Capture post-request context
        $responseContext = $this->captureResponseContext($response, $startTime);
        
        // Determine if this requires audit logging
        if ($this->shouldAuditRequest($request, $response)) {
            $this->createAuditLog($request, $response, $requestContext, $responseContext);
        }
        
        return $response;
    }

    /**
     * Capture request context for audit logging.
     */
    private function captureRequestContext(Request $request): array
    {
        $user = auth()->user();
        
        return [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_type' => $user?->user_type,
            'user_role' => $user?->getRoleNames()->first(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'request_id' => $request->header('X-Request-ID') ?: \Illuminate\Support\Str::uuid(),
            'route_name' => $request->route()?->getName(),
            'http_method' => $request->method(),
            'url' => $request->fullUrl(),
            'referer' => $request->header('referer'),
            'request_data' => $this->sanitizeRequestData($request),
            'geo_location' => $this->getGeoLocation($request->ip()),
        ];
    }

    /**
     * Capture response context for audit logging.
     */
    private function captureResponseContext(Response $response, float $startTime): array
    {
        $endTime = microtime(true);
        
        return [
            'response_status' => $response->getStatusCode(),
            'response_time_ms' => round(($endTime - $startTime) * 1000, 2),
            'access_granted' => $response->getStatusCode() < 400,
            'response_size_bytes' => strlen($response->getContent()),
        ];
    }

    /**
     * Determine if this request should be audited.
     */
    private function shouldAuditRequest(Request $request, Response $response): bool
    {
        // Always audit if user is authenticated
        if (auth()->check()) {
            return true;
        }

        // Audit failed authentication attempts
        if ($response->getStatusCode() === 401) {
            return true;
        }

        // Audit access to sensitive routes
        $sensitiveRoutes = [
            'prescription.*',
            'patient.*',
            'medical.*',
            'compliance.*',
            'admin.*',
            'api.*',
        ];

        $routeName = $request->route()?->getName() ?? '';
        foreach ($sensitiveRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        // Audit based on URL patterns
        $sensitiveUrlPatterns = [
            '/api/',
            '/admin/',
            '/prescription',
            '/patient',
            '/compliance',
            '/reports',
        ];

        $path = $request->path();
        foreach ($sensitiveUrlPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create comprehensive audit log.
     */
    private function createAuditLog(
        Request $request, 
        Response $response, 
        array $requestContext, 
        array $responseContext
    ): void {
        try {
            $eventType = $this->determineEventType($request, $response);
            $entityInfo = $this->extractEntityInfo($request);
            $complianceFlags = $this->determineComplianceFlags($request, $response);
            $riskLevel = $this->calculateRiskLevel($request, $response, $requestContext);
            
            $auditData = array_merge($requestContext, $responseContext, [
                'event_type' => $eventType,
                'entity_type' => $entityInfo['type'],
                'entity_id' => $entityInfo['id'],
                'entity_identifier' => $entityInfo['identifier'],
                'description' => $this->generateDescription($request, $response, $eventType),
                'metadata' => $this->buildMetadata($request, $response),
                'risk_level' => $riskLevel,
                'data_classification' => $this->determineDataClassification($request),
                'source_system' => config('app.name'),
                'environment' => config('app.env'),
                'application_version' => config('app.version'),
            ], $complianceFlags);

            AuditLog::logEvent(
                $eventType,
                $entityInfo['type'],
                $entityInfo['id'],
                $auditData
            );

            // Log high-risk events to security channel
            if ($riskLevel === AuditLog::RISK_HIGH || $riskLevel === AuditLog::RISK_CRITICAL) {
                Log::channel('security')->warning('High-risk activity detected', $auditData);
            }

            // Log PHI access to HIPAA channel
            if ($complianceFlags['is_phi_access'] ?? false) {
                Log::channel('hipaa')->info('PHI access event', $auditData);
            }

            // Log controlled substance access to DEA channel
            if ($complianceFlags['is_controlled_substance'] ?? false) {
                Log::channel('pharmacy')->info('Controlled substance access', $auditData);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'request_url' => $request->fullUrl(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Determine the event type based on request characteristics.
     */
    private function determineEventType(Request $request, Response $response): string
    {
        $method = $request->method();
        $route = $request->route()?->getName() ?? '';
        $path = $request->path();

        // Authentication events
        if (str_contains($route, 'login')) {
            return $response->getStatusCode() < 400 ? AuditLog::EVENT_LOGIN : AuditLog::EVENT_FAILED_LOGIN;
        }

        if (str_contains($route, 'logout')) {
            return AuditLog::EVENT_LOGOUT;
        }

        // Prescription events
        if (str_contains($path, 'prescription')) {
            return match ($method) {
                'POST' => AuditLog::EVENT_PRESCRIPTION_CREATED,
                'GET' => AuditLog::EVENT_ACCESSED,
                'PUT', 'PATCH' => AuditLog::EVENT_UPDATED,
                'DELETE' => AuditLog::EVENT_DELETED,
                default => AuditLog::EVENT_ACCESSED,
            };
        }

        // Patient/PHI events
        if (str_contains($path, 'patient') || str_contains($path, 'profile')) {
            return match ($method) {
                'GET' => AuditLog::EVENT_PATIENT_PROFILE_ACCESSED,
                'POST' => AuditLog::EVENT_CREATED,
                'PUT', 'PATCH' => AuditLog::EVENT_UPDATED,
                'DELETE' => AuditLog::EVENT_DELETED,
                default => AuditLog::EVENT_ACCESSED,
            };
        }

        // Medical record events
        if (str_contains($path, 'medical') || str_contains($path, 'record')) {
            return AuditLog::EVENT_MEDICAL_RECORD_ACCESSED;
        }

        // Payment events
        if (str_contains($path, 'payment') || str_contains($path, 'billing')) {
            return AuditLog::EVENT_PAYMENT_PROCESSED;
        }

        // Data export events
        if (str_contains($path, 'export') || str_contains($path, 'download')) {
            return AuditLog::EVENT_DATA_EXPORT;
        }

        // System configuration events
        if (str_contains($path, 'config') || str_contains($path, 'settings')) {
            return AuditLog::EVENT_SYSTEM_CONFIG_CHANGED;
        }

        // Generic CRUD events
        return match ($method) {
            'GET' => AuditLog::EVENT_VIEWED,
            'POST' => AuditLog::EVENT_CREATED,
            'PUT', 'PATCH' => AuditLog::EVENT_UPDATED,
            'DELETE' => AuditLog::EVENT_DELETED,
            default => AuditLog::EVENT_ACCESSED,
        };
    }

    /**
     * Extract entity information from request.
     */
    private function extractEntityInfo(Request $request): array
    {
        $route = $request->route();
        $path = $request->path();

        // Extract from route parameters
        if ($route) {
            $parameters = $route->parameters();
            
            // Look for common entity patterns
            foreach (['prescription', 'user', 'patient', 'order', 'payment'] as $entity) {
                if (isset($parameters[$entity])) {
                    return [
                        'type' => ucfirst($entity),
                        'id' => is_object($parameters[$entity]) ? $parameters[$entity]->id : $parameters[$entity],
                        'identifier' => is_object($parameters[$entity]) ? 
                            ($parameters[$entity]->name ?? $parameters[$entity]->id) : $parameters[$entity],
                    ];
                }
            }

            // Look for ID parameter
            if (isset($parameters['id'])) {
                $entityType = $this->guessEntityTypeFromPath($path);
                return [
                    'type' => $entityType,
                    'id' => $parameters['id'],
                    'identifier' => $parameters['id'],
                ];
            }
        }

        // Extract from URL path
        if (preg_match('/\/(\w+)\/(\d+)/', $path, $matches)) {
            return [
                'type' => ucfirst($matches[1]),
                'id' => (int) $matches[2],
                'identifier' => $matches[2],
            ];
        }

        return [
            'type' => $this->guessEntityTypeFromPath($path),
            'id' => null,
            'identifier' => null,
        ];
    }

    /**
     * Guess entity type from URL path.
     */
    private function guessEntityTypeFromPath(string $path): ?string
    {
        $pathSegments = explode('/', trim($path, '/'));
        
        $entityMappings = [
            'prescription' => 'Prescription',
            'prescriptions' => 'Prescription',
            'patient' => 'User',
            'patients' => 'User',
            'user' => 'User',
            'users' => 'User',
            'order' => 'Order',
            'orders' => 'Order',
            'payment' => 'Payment',
            'payments' => 'Payment',
            'product' => 'Product',
            'products' => 'Product',
            'compliance' => 'ComplianceReport',
            'report' => 'ComplianceReport',
            'reports' => 'ComplianceReport',
        ];

        foreach ($pathSegments as $segment) {
            if (isset($entityMappings[$segment])) {
                return $entityMappings[$segment];
            }
        }

        return null;
    }

    /**
     * Determine compliance flags (PHI, controlled substance, financial).
     */
    private function determineComplianceFlags(Request $request, Response $response): array
    {
        $path = $request->path();
        $route = $request->route()?->getName() ?? '';
        
        $flags = [
            'is_phi_access' => false,
            'is_controlled_substance' => false,
            'is_financial_data' => false,
            'requires_retention' => true,
            'retention_years' => 7,
        ];

        // PHI access detection
        $phiPatterns = [
            'patient', 'profile', 'medical', 'prescription', 'health', 'diagnosis', 'treatment'
        ];
        
        foreach ($phiPatterns as $pattern) {
            if (str_contains($path, $pattern) || str_contains($route, $pattern)) {
                $flags['is_phi_access'] = true;
                $flags['retention_years'] = 6; // HIPAA requirement
                break;
            }
        }

        // Controlled substance detection
        if (str_contains($path, 'prescription') || str_contains($path, 'controlled')) {
            // Check if the prescription involves controlled substances
            $routeParameters = $request->route()?->parameters() ?? [];
            if (isset($routeParameters['prescription'])) {
                $prescription = $routeParameters['prescription'];
                if (is_object($prescription) && isset($prescription->is_controlled_substance)) {
                    $flags['is_controlled_substance'] = $prescription->is_controlled_substance;
                    if ($flags['is_controlled_substance']) {
                        $flags['retention_years'] = 2; // DEA requirement
                    }
                }
            }
        }

        // Financial data detection
        $financialPatterns = [
            'payment', 'billing', 'invoice', 'transaction', 'credit', 'bank'
        ];
        
        foreach ($financialPatterns as $pattern) {
            if (str_contains($path, $pattern) || str_contains($route, $pattern)) {
                $flags['is_financial_data'] = true;
                $flags['retention_years'] = 3; // PCI DSS requirement
                break;
            }
        }

        return $flags;
    }

    /**
     * Calculate risk level based on various factors.
     */
    private function calculateRiskLevel(Request $request, Response $response, array $context): string
    {
        $riskScore = 0;

        // Failed access adds risk
        if (!($context['access_granted'] ?? true)) {
            $riskScore += 3;
        }

        // PHI access adds risk
        if (str_contains($request->path(), 'patient') || str_contains($request->path(), 'medical')) {
            $riskScore += 2;
        }

        // Controlled substance access adds risk
        if (str_contains($request->path(), 'prescription')) {
            $riskScore += 2;
        }

        // Administrative actions add risk
        if (str_contains($request->path(), 'admin') || str_contains($request->path(), 'config')) {
            $riskScore += 3;
        }

        // After-hours access adds risk
        $hour = now()->hour;
        if ($hour < 6 || $hour > 22) {
            $riskScore += 1;
        }

        // Weekend access adds risk
        if (now()->isWeekend()) {
            $riskScore += 1;
        }

        // Bulk operations add risk
        if ($request->method() === 'POST' && str_contains($request->path(), 'bulk')) {
            $riskScore += 2;
        }

        // Data export adds risk
        if (str_contains($request->path(), 'export') || str_contains($request->path(), 'download')) {
            $riskScore += 2;
        }

        // Determine risk level
        return match (true) {
            $riskScore >= 7 => AuditLog::RISK_CRITICAL,
            $riskScore >= 4 => AuditLog::RISK_HIGH,
            $riskScore >= 2 => AuditLog::RISK_MEDIUM,
            default => AuditLog::RISK_LOW,
        };
    }

    /**
     * Determine data classification.
     */
    private function determineDataClassification(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'patient') || str_contains($path, 'medical') || str_contains($path, 'prescription')) {
            return AuditLog::DATA_PHI;
        }

        if (str_contains($path, 'payment') || str_contains($path, 'billing')) {
            return AuditLog::DATA_PCI;
        }

        if (str_contains($path, 'admin') || str_contains($path, 'config')) {
            return AuditLog::DATA_CONFIDENTIAL;
        }

        return AuditLog::DATA_INTERNAL;
    }

    /**
     * Generate descriptive text for the audit log.
     */
    private function generateDescription(Request $request, Response $response, string $eventType): string
    {
        $user = auth()->user();
        $userName = $user?->name ?? 'Anonymous';
        $method = $request->method();
        $path = $request->path();
        $status = $response->getStatusCode();

        $action = match ($eventType) {
            AuditLog::EVENT_LOGIN => 'logged in',
            AuditLog::EVENT_LOGOUT => 'logged out',
            AuditLog::EVENT_FAILED_LOGIN => 'failed login attempt',
            AuditLog::EVENT_PRESCRIPTION_CREATED => 'created prescription',
            AuditLog::EVENT_PRESCRIPTION_DISPENSED => 'dispensed prescription',
            AuditLog::EVENT_PATIENT_PROFILE_ACCESSED => 'accessed patient profile',
            AuditLog::EVENT_MEDICAL_RECORD_ACCESSED => 'accessed medical record',
            AuditLog::EVENT_PAYMENT_PROCESSED => 'processed payment',
            AuditLog::EVENT_DATA_EXPORT => 'exported data',
            AuditLog::EVENT_CREATED => 'created record',
            AuditLog::EVENT_UPDATED => 'updated record',
            AuditLog::EVENT_DELETED => 'deleted record',
            AuditLog::EVENT_VIEWED => 'viewed record',
            default => 'performed action',
        };

        $statusText = $status < 400 ? 'successfully' : 'unsuccessfully';

        return "{$userName} {$statusText} {$action} via {$method} {$path} (HTTP {$status})";
    }

    /**
     * Build metadata for the audit log.
     */
    private function buildMetadata(Request $request, Response $response): array
    {
        return [
            'request_headers' => $this->sanitizeHeaders($request->headers->all()),
            'query_parameters' => $request->query(),
            'route_parameters' => $request->route()?->parameters() ?? [],
            'response_headers' => $this->sanitizeHeaders($response->headers->all()),
            'browser_info' => $this->extractBrowserInfo($request->userAgent()),
            'is_ajax' => $request->ajax(),
            'is_json' => $request->wantsJson(),
            'content_type' => $request->header('content-type'),
            'accept' => $request->header('accept'),
        ];
    }

    /**
     * Sanitize request data to remove sensitive information.
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
            'credit_card_number',
            'cvv',
            'ssn',
            'social_security_number',
            'bank_account_number',
            'routing_number',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Sanitize headers to remove sensitive information.
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'x-api-key',
            'cookie',
            'set-cookie',
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = '[REDACTED]';
            }
        }

        return $headers;
    }

    /**
     * Extract browser information from user agent.
     */
    private function extractBrowserInfo(?string $userAgent): array
    {
        if (!$userAgent) {
            return ['browser' => 'Unknown', 'platform' => 'Unknown'];
        }

        // Simple browser detection
        $browsers = [
            'Chrome' => '/Chrome\/([0-9.]+)/',
            'Firefox' => '/Firefox\/([0-9.]+)/',
            'Safari' => '/Safari\/([0-9.]+)/',
            'Edge' => '/Edge\/([0-9.]+)/',
            'Internet Explorer' => '/MSIE ([0-9.]+)/',
        ];

        $browser = 'Unknown';
        $version = 'Unknown';

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $browser = $name;
                $version = $matches[1] ?? 'Unknown';
                break;
            }
        }

        // Simple platform detection
        $platform = 'Unknown';
        if (str_contains($userAgent, 'Windows')) $platform = 'Windows';
        elseif (str_contains($userAgent, 'Mac')) $platform = 'macOS';
        elseif (str_contains($userAgent, 'Linux')) $platform = 'Linux';
        elseif (str_contains($userAgent, 'Android')) $platform = 'Android';
        elseif (str_contains($userAgent, 'iOS')) $platform = 'iOS';

        return [
            'browser' => $browser,
            'version' => $version,
            'platform' => $platform,
            'full_user_agent' => $userAgent,
        ];
    }

    /**
     * Get geo-location information from IP address.
     */
    private function getGeoLocation(string $ip): array
    {
        // In a real implementation, you would use a geo-location service
        // For now, return basic information
        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'is_internal' => $this->isInternalIP($ip),
        ];
    }

    /**
     * Check if IP address is internal/private.
     */
    private function isInternalIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}