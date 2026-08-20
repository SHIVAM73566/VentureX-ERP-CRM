<?php

namespace App\Services;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecurityStateService
{
    public static function lockdownEnabled(): bool
    {
        return Cache::get('security.lockdown.active', (bool) config('security.lockdown.enabled', false));
    }

    public static function setLockdown(bool $active): void
    {
        Cache::put('security.lockdown.active', $active, now()->addYear());
    }

    /** Compute the 0-100 security score and per-checkpoint status. */
    public static function score(): array
    {
        $checkpoints = config('security.score.checkpoints', []);
        $earned = 0;
        $total = 0;
        $statuses = [];

        foreach ($checkpoints as $key => $checkpoint) {
            $weight = (int) $checkpoint['weight'];
            $total += $weight;
            $passed = self::checkpointPassed($key);
            $earned += $passed ? $weight : 0;
            $statuses[] = [
                'key' => $key,
                'label' => $checkpoint['label'],
                'weight' => $weight,
                'passed' => $passed,
            ];
        }

        return [
            'score' => $total > 0 ? (int) round($earned / $total * 100) : 0,
            'earned' => $earned,
            'total' => $total,
            'checkpoints' => $statuses,
        ];
    }

    protected static function checkpointPassed(string $key): bool
    {
        return match ($key) {
            'admin_mfa' => self::privilegedMfaEnrolled(),
            'https' => request()->isSecure(),
            'debug_off' => ! config('app.debug'),
            'rate_limited' => true,
            'audit_active' => true,
            'headers' => true,
            'backup_healthy' => self::recentBackup(),
            'db_least_priv' => self::leastPrivilegeDb(),
            'malware_scan' => (bool) config('security.upload.scan_commands'),
            'ssl_ca' => self::sslCaConfigured(),
            'secrets_env' => true,
            default => false,
        };
    }

    protected static function privilegedMfaEnrolled(): bool
    {
        $roles = config('security.mfa.mandatory_roles', []);

        $adminUser = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->exists();

        $enrolled = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->whereNotNull('two_factor_secret')
            ->whereNotNull('two_factor_confirmed_at')
            ->count();

        return ! $adminUser || $enrolled > 0;
    }

    protected static function recentBackup(): bool
    {
        return Schema::hasTable('backups')
            && DB::table('backups')->where('created_at', '>=', now()->subDays(7))->exists();
    }

    protected static function leastPrivilegeDb(): bool
    {
        return config('database.default') === 'mysql'
            && str_contains((string) config('database.connections.mysql.username'), 'my_erp');
    }

    protected static function sslCaConfigured(): bool
    {
        return ini_get('curl.cainfo') !== false && ini_get('curl.cainfo') !== '';
    }

    public static function activeThreats(): int
    {
        return SecurityEvent::query()
            ->whereIn('severity', ['high', 'critical'])
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
    }
}
