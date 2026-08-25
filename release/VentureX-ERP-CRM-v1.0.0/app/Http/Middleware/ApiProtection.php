<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Rate Limiter & Protection Middleware
 *
 * Protects CRM & ERP from:
 * - Brute force attacks
 * - API abuse
 * - DDoS attempts
 * - Unauthorized access
 */
class ApiProtection
{
    /**
     * Rate limit tiers (requests per minute)
     */
    private const RATE_LIMITS = [
        'public' => 30,        // Public API endpoints
        'authenticated' => 120, // Authenticated users
        'admin' => 300,        // Admin users
        'ai' => 20,           // AI endpoints (expensive)
        'export' => 5,        // Export endpoints (resource heavy)
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $tier = 'authenticated'): Response
    {
        // 1. Rate limiting
        $rateLimit = self::RATE_LIMITS[$tier] ?? self::RATE_LIMITS['authenticated'];
        $key = $this->getRateLimitKey($request, $tier);

        if (RateLimiter::tooManyAttempts($key, $rateLimit)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'error' => 'Too many requests',
                'message' => "Rate limit exceeded. Try again in {$retryAfter} seconds.",
                'retry_after' => $retryAfter,
                'reference' => 'VentureX-ERP-2026-01483863236',
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $rateLimit,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, 60);

        // 2. IP blocking check
        $clientIp = $request->ip();
        if ($this->isBlockedIp($clientIp)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your IP has been temporarily blocked.',
                'reference' => 'VentureX-ERP-2026-01483863236',
            ], 403);
        }

        // 3. Suspicious request detection
        if ($this->isSuspiciousRequest($request)) {
            $this->logSuspiciousActivity($request);

            // Don't block, but add extra logging
            logger()->warning('Suspicious API request detected', [
                'ip' => $clientIp,
                'url' => $request->url(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'reference' => 'VentureX-ERP-2026-01483863236',
            ]);
        }

        // 4. Add security headers
        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('X-RateLimit-Limit', $rateLimit);
            $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $rateLimit));
        }

        return $response;
    }

    /**
     * Generate rate limit key
     */
    private function getRateLimitKey(Request $request, string $tier): string
    {
        $identifier = $request->user()?->id ?? $request->ip();

        return "api_rate:{$tier}:{$identifier}";
    }

    /**
     * Check if IP is blocked
     */
    private function isBlockedIp(string $ip): bool
    {
        $blockedIps = cache()->get('blocked_ips', []);

        return in_array($ip, $blockedIps);
    }

    /**
     * Block an IP address
     */
    public static function blockIp(string $ip, int $minutes = 60): void
    {
        $blockedIps = cache()->get('blocked_ips', []);
        if (! in_array($ip, $blockedIps)) {
            $blockedIps[] = $ip;
            cache()->put('blocked_ips', $blockedIps, now()->addMinutes($minutes));
        }
    }

    /**
     * Detect suspicious requests
     */
    private function isSuspiciousRequest(Request $request): bool
    {
        $suspiciousPatterns = [
            'union select',
            'insert into',
            'drop table',
            'delete from',
            '<script',
            'javascript:',
            'eval(',
            'exec(',
            '../../../',
            'etc/passwd',
            'wp-admin',
            'wp-login',
            'xmlrpc.php',
        ];

        $checkString = strtolower(
            $request->url().' '.
            ($request->getQueryString() ?? '').' '.
            $request->input('email', '').' '.
            $request->input('password', '').' '.
            $request->input('q', '')
        );

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($checkString, $pattern)) {
                return true;
            }
        }

        // Check for SQL injection attempts
        if (preg_match('/(\%27)|(\')|(\-\-)|(\%23)|(#)/i', $checkString)) {
            return true;
        }

        return false;
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity(Request $request): void
    {
        $logEntry = [
            'timestamp' => now()->toIso8601String(),
            'ip' => $request->ip(),
            'url' => mb_substr($request->url(), 0, 255),
            'method' => $request->method(),
            'user_agent' => mb_substr($request->userAgent() ?? '', 0, 200),
            'user_id' => $request->user()?->id,
        ];

        $key = 'suspicious_activity:'.md5($request->ip().$request->url());
        cache()->put($key, $logEntry, now()->addHours(24));
    }
}
