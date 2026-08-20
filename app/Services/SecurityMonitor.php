<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Security Monitor for VentureX ERP & CRM
 *
 * Tracks and protects against:
 * - Brute force login attempts
 * - Unauthorized data access
 * - API abuse
 * - Suspicious activities
 */
class SecurityMonitor
{
    /**
     * Maximum failed login attempts before lockout
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes
     */
    private const LOCKOUT_MINUTES = 15;

    /**
     * Track failed login attempt
     */
    public static function recordFailedLogin(string $email, string $ip): array
    {
        $key = "login_attempts:{$email}:{$ip}";
        $attempts = Cache::get($key, 0);
        $attempts++;

        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        $remaining = self::MAX_LOGIN_ATTEMPTS - $attempts;

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            // Lock the account
            $lockKey = "account_locked:{$email}";
            Cache::put($lockKey, true, now()->addMinutes(self::LOCKOUT_MINUTES));

            // Log the lockout
            self::logSecurityEvent('account_locked', [
                'email' => $email,
                'ip' => $ip,
                'attempts' => $attempts,
                'reference' => 'VentureX-ERP-2026-01483863236',
            ]);

            return [
                'locked' => true,
                'message' => 'Account locked due to too many failed attempts.',
                'retry_after' => self::LOCKOUT_MINUTES * 60,
            ];
        }

        return [
            'locked' => false,
            'attempts' => $attempts,
            'remaining' => $remaining,
            'message' => "Invalid credentials. {$remaining} attempts remaining.",
        ];
    }

    /**
     * Check if account is locked
     */
    public static function isAccountLocked(string $email): bool
    {
        return Cache::has("account_locked:{$email}");
    }

    /**
     * Clear failed login attempts (on successful login)
     */
    public static function clearLoginAttempts(string $email, string $ip): void
    {
        Cache::forget("login_attempts:{$email}:{$ip}");
        Cache::forget("account_locked:{$email}");
    }

    /**
     * Record successful login
     */
    public static function recordSuccessfulLogin(int $userId, string $ip): void
    {
        $key = "last_login:{$userId}";
        Cache::put($key, [
            'ip' => $ip,
            'timestamp' => now()->toIso8601String(),
        ], now()->addDays(30));

        self::logSecurityEvent('login_success', [
            'user_id' => $userId,
            'ip' => $ip,
            'reference' => 'VentureX-ERP-2026-01483863236',
        ]);
    }

    /**
     * Check for suspicious data access patterns
     */
    public static function checkDataAccess(int $userId, string $table, string $action): bool
    {
        $key = "data_access:{$userId}:{$table}";
        $accessLog = Cache::get($key, []);

        // Record this access
        $accessLog[] = [
            'action' => $action,
            'timestamp' => now()->timestamp,
        ];

        // Keep only last 100 entries per table
        if (count($accessLog) > 100) {
            $accessLog = array_slice($accessLog, -100);
        }

        Cache::put($key, $accessLog, now()->addHours(1));

        // Check for suspicious patterns
        $recentAccess = array_filter($accessLog, function ($entry) {
            return $entry['timestamp'] > now()->subMinutes(5)->timestamp;
        });

        // If user accessed 50+ records in 5 minutes, flag it
        if (count($recentAccess) > 50) {
            self::logSecurityEvent('suspicious_data_access', [
                'user_id' => $userId,
                'table' => $table,
                'access_count' => count($recentAccess),
                'reference' => 'VentureX-ERP-2026-01483863236',
            ]);

            return false; // Suspicious
        }

        return true; // Normal
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent(string $type, array $data = []): void
    {
        $event = [
            'type' => $type,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
            'reference' => 'VentureX-ERP-2026-01483863236',
        ];

        // Store in security logs table
        try {
            DB::table('security_events')->insert([
                'type' => $type,
                'data' => json_encode($data),
                'ip' => request()->ip(),
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fallback to cache if table doesn't exist
            $logs = Cache::get('security_logs', []);
            $logs[] = $event;
            if (count($logs) > 10000) {
                $logs = array_slice($logs, -10000);
            }
            Cache::put('security_logs', $logs, now()->addDays(30));
        }
    }

    /**
     * Get security dashboard data
     */
    public static function getSecurityDashboard(): array
    {
        return [
            'reference' => 'VentureX-ERP-2026-01483863236',
            'locked_accounts' => self::getLockedAccounts(),
            'recent_logins' => self::getRecentLogins(),
            'suspicious_activity' => self::getSuspiciousActivity(),
            'blocked_ips' => cache()->get('blocked_ips', []),
            'security_events' => self::getRecentSecurityEvents(),
        ];
    }

    /**
     * Get locked accounts
     */
    private static function getLockedAccounts(): array
    {
        // This would query the database in production
        return [];
    }

    /**
     * Get recent logins
     */
    private static function getRecentLogins(): array
    {
        // This would query the database in production
        return [];
    }

    /**
     * Get suspicious activity
     */
    private static function getSuspiciousActivity(): array
    {
        return cache()->get('suspicious_activity', []);
    }

    /**
     * Get recent security events
     */
    private static function getRecentSecurityEvents(): array
    {
        try {
            return DB::table('security_events')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return cache()->get('security_logs', []);
        }
    }
}
