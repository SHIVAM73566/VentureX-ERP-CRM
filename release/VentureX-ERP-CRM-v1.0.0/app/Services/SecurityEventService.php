<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Central security event store with alert thresholds.
 */
class SecurityEventService
{
    /**
     * Record a security event. Thresholds trigger escalated alerts.
     *
     * @param  array<string, mixed>  $details
     */
    public static function record(
        string $category,
        string $event,
        string $title,
        string $severity = 'low',
        ?User $user = null,
        ?Model $company = null,
        array $details = [],
        int $dedupeWindowMinutes = 5,
    ): SecurityEvent {
        $userId = $user?->id ?? auth()->id();
        $companyId = $company?->getKey() ?? CompanyContext::id();

        $eventModel = SecurityEvent::create([
            'correlation_id' => CorrelationContext::id(),
            'severity' => $severity,
            'category' => $category,
            'event' => $event,
            'title' => static::redactEmail($title),
            'details' => static::redactDetails($details),
            'user_id' => $userId,
            'company_id' => $companyId,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
        ]);

        return $eventModel;
    }

    /**
     * Redact email addresses from a string.
     */
    protected static function redactEmail(string $text): string
    {
        return preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[REDACTED_EMAIL]', $text);
    }

    /**
     * Redact emails from details array values.
     */
    protected static function redactDetails(array $details): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[REDACTED_EMAIL]', $value);
            }

            return $value;
        }, $details);
    }

    /** Convenience alert when an anomaly repeats past its threshold. */
    public static function alertThreshold(string $category, string $event, string $title, int $count, int $threshold, string $baseSeverity = 'medium'): ?SecurityEvent
    {
        if ($count < $threshold) {
            return null;
        }

        $severity = match (true) {
            $count >= $threshold * 5 => 'critical',
            $count >= $threshold * 3 => 'high',
            default => $baseSeverity,
        };

        return static::record($category, $event, static::redactEmail($title), $severity, null, null, ['count' => $count, 'threshold' => $threshold]);
    }

    /** Record a login attempt row for audit + anomaly detection. Email is hashed for privacy. */
    public static function recordLogin(
        string $email,
        bool $success,
        ?User $user = null,
        string $reason = '',
    ): LoginAttempt {
        return LoginAttempt::create([
            'user_id' => $user?->id,
            'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '[REDACTED]',
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
            'device_fingerprint' => DeviceFingerprint::current(),
            'success' => $success,
            'reason' => $reason ?: null,
            'correlation_id' => CorrelationContext::id(),
        ]);
    }

    /** Detect an account under attack and emit an alert when appropriate. */
    public static function evaluateLoginPattern(string $email): void
    {
        $recentFailures = LoginAttempt::query()
            ->where('email', $email)
            ->where('success', false)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        $uniqueIps = LoginAttempt::query()
            ->where('email', $email)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->distinct('ip')
            ->count('ip');

        $threshold = (int) config('security.login.alert_after', 10);

        static::alertThreshold('auth', 'repeated_failed_logins', 'Repeated failed logins detected', $recentFailures, $threshold, 'medium');

        if ($recentFailures >= $threshold && $uniqueIps > 3) {
            static::record(
                'auth',
                'credential_stuffing',
                "Possible credential stuffing detected from {$uniqueIps} distinct IPs",
                'high',
                null,
                null,
                ['unique_ips' => $uniqueIps, 'failures' => $recentFailures],
                dedupeWindowMinutes: 15,
            );
        }
    }

    /** Detect impossible travel based on the previous successful login. */
    public static function detectImpossibleTravel(User $user, string $currentIp): void
    {
        $previous = $user->last_login_at;
        if (! $previous || $previous->lt(now()->subDay())) {
            return;
        }

        $last = LoginAttempt::query()
            ->where('user_id', $user->id)
            ->where('success', true)
            ->latest('id')
            ->first();

        if ($last && $last->ip !== $currentIp) {
            static::record(
                'auth',
                'ip_change',
                "Successful login from new IP (previous {$last->ip})",
                'medium',
                $user,
                null,
                ['previous_ip' => $last->ip, 'current_ip' => $currentIp],
                dedupeWindowMinutes: 15,
            );
        }
    }
}
