<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PaymentSecurityMiddleware
{
    /**
     * Handle an incoming request for payment operations.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): ResponseAlias
    {
        // Security checks for payment requests
        $this->performSecurityChecks($request);

        // Rate limiting for payment attempts
        $this->applyRateLimiting($request);

        // Log payment security events
        $this->logSecurityEvent($request);

        return $next($request);
    }

    /**
     * Perform various security checks
     */
    protected function performSecurityChecks(Request $request): void
    {
        // Check for suspicious IP addresses
        $this->checkSuspiciousIP($request);

        // Validate request headers for security
        $this->validateSecurityHeaders($request);

        // Check for bot/automated requests
        $this->checkBotActivity($request);

        // Validate geo-location restrictions
        $this->checkGeoRestrictions($request);
    }

    /**
     * Apply rate limiting for payment operations
     */
    protected function applyRateLimiting(Request $request): void
    {
        $key = 'payment_attempts:' . $request->ip();
        
        // Allow 10 payment attempts per minute per IP
        if (RateLimiter::tooManyAttempts($key, 10)) {
            Log::warning('Payment rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
            ]);
            
            abort(429, 'Too many payment attempts. Please try again later.');
        }

        RateLimiter::hit($key, 60); // 60 seconds
    }

    /**
     * Check for suspicious IP addresses
     */
    protected function checkSuspiciousIP(Request $request): void
    {
        $ip = $request->ip();
        
        // List of blocked IP ranges (in production, this would come from a database)
        $blockedIPs = [
            // Add known malicious IPs or ranges
        ];

        // Check if IP is in blocklist
        foreach ($blockedIPs as $blockedIP) {
            if ($this->ipInRange($ip, $blockedIP)) {
                Log::alert('Blocked IP attempted payment', [
                    'ip' => $ip,
                    'blocked_ip' => $blockedIP,
                ]);
                
                abort(403, 'Access denied');
            }
        }

        // Check for Tor exit nodes (basic check)
        if ($this->isTorExitNode($ip)) {
            Log::warning('Tor exit node payment attempt', ['ip' => $ip]);
            
            // You might want to block or require additional verification
            // abort(403, 'Payments from Tor networks are not allowed');
        }
    }

    /**
     * Validate security headers
     */
    protected function validateSecurityHeaders(Request $request): void
    {
        // Check for missing or suspicious headers
        $userAgent = $request->userAgent();
        
        if (empty($userAgent) || $this->isSuspiciousUserAgent($userAgent)) {
            Log::warning('Suspicious user agent in payment request', [
                'user_agent' => $userAgent,
                'ip' => $request->ip(),
            ]);
        }

        // Check for common bot patterns
        if ($this->isKnownBot($userAgent)) {
            Log::info('Bot detected in payment request', [
                'user_agent' => $userAgent,
                'ip' => $request->ip(),
            ]);
            
            // You might want to block bots from payment operations
            // abort(403, 'Automated requests are not allowed for payments');
        }
    }

    /**
     * Check for bot activity patterns
     */
    protected function checkBotActivity(Request $request): void
    {
        $patterns = [
            'rapid_requests' => $this->checkRapidRequests($request),
            'missing_referrer' => empty($request->header('referer')),
            'suspicious_timing' => $this->checkSuspiciousTiming($request),
        ];

        $botScore = array_sum($patterns);
        
        if ($botScore >= 2) {
            Log::warning('Potential bot activity in payment', [
                'ip' => $request->ip(),
                'patterns' => $patterns,
                'score' => $botScore,
            ]);
            
            // Could implement CAPTCHA or additional verification here
        }
    }

    /**
     * Check geographical restrictions
     */
    protected function checkGeoRestrictions(Request $request): void
    {
        // In production, you would use a geo-IP service
        $country = $this->getCountryFromIP($request->ip());
        
        // List of restricted countries for payments
        $restrictedCountries = [
            // Add countries where payments are not allowed
            // 'IR', 'KP', 'SY', etc.
        ];

        if (in_array($country, $restrictedCountries)) {
            Log::alert('Payment attempt from restricted country', [
                'ip' => $request->ip(),
                'country' => $country,
            ]);
            
            abort(403, 'Payments are not available in your region');
        }
    }

    /**
     * Log security events for audit trail
     */
    protected function logSecurityEvent(Request $request): void
    {
        Log::info('Payment security middleware executed', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'path' => $request->path(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Helper methods
     */

    protected function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $mask) = explode('/', $range);
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
    }

    protected function isTorExitNode(string $ip): bool
    {
        // Simple check - in production, use a proper Tor exit node list
        // This is a placeholder implementation
        return false;
    }

    protected function isSuspiciousUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            'curl',
            'wget',
            'python',
            'bot',
            'spider',
            'crawler',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function isKnownBot(string $userAgent): bool
    {
        $knownBots = [
            'Googlebot',
            'Bingbot',
            'Slurp',
            'DuckDuckBot',
            'Baiduspider',
            'YandexBot',
            'facebookexternalhit',
            'Twitterbot',
            'LinkedInBot',
        ];

        foreach ($knownBots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function checkRapidRequests(Request $request): bool
    {
        $key = 'rapid_requests:' . $request->ip();
        $attempts = RateLimiter::attempts($key);
        
        // Consider it rapid if more than 5 requests in the last minute
        return $attempts > 5;
    }

    protected function checkSuspiciousTiming(Request $request): bool
    {
        // Check if request timing patterns suggest automation
        // This is a placeholder - you'd implement more sophisticated timing analysis
        return false;
    }

    protected function getCountryFromIP(string $ip): ?string
    {
        // Placeholder for geo-IP lookup
        // In production, use a service like MaxMind GeoIP2 or similar
        return null;
    }
}