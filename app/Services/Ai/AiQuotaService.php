<?php

namespace App\Services\Ai;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Enforces daily and weekly AI quotas per user based on role-based defaults
 * with optional per-company overrides stored in the settings table.
 */
class AiQuotaService
{
    public function __construct(
        protected AiUsageMonitor $usage,
    ) {}

    /**
     * Check whether the current user has quota remaining for today and this week.
     */
    public function check(): array
    {
        $user = auth()->user();
        if (! $user) {
            return $this->denied();
        }

        $limits = $this->resolveLimits($user);
        $dailyUsed = $this->dailyUsed($user->id);
        $weeklyUsed = $this->weeklyUsed($user->id);

        $dailyRemaining = max(0, $limits['daily'] - $dailyUsed);
        $weeklyRemaining = max(0, $limits['weekly'] - $weeklyUsed);

        $allowed = $dailyRemaining > 0 && $weeklyRemaining > 0;

        $alerts = $this->computeAlerts($dailyUsed, $limits['daily'], $weeklyUsed, $limits['weekly']);

        return [
            'allowed' => $allowed,
            'daily_remaining' => $dailyRemaining,
            'weekly_remaining' => $weeklyRemaining,
            'daily_limit' => $limits['daily'],
            'weekly_limit' => $limits['weekly'],
            'daily_used' => $dailyUsed,
            'weekly_used' => $weeklyUsed,
            'alerts' => $alerts,
        ];
    }

    public function record(int $userId): void
    {
        $day = now()->format('Y-m-d');
        $week = now()->format('Y-W');

        Cache::increment("ai:quota:day:{$userId}:{$day}", 1, 172800);
        Cache::increment("ai:quota:week:{$userId}:{$week}", 1, 604800);
    }

    public function dailyUsed(int $userId): int
    {
        $day = now()->format('Y-m-d');

        return (int) Cache::get("ai:quota:day:{$userId}:{$day}", 0);
    }

    public function weeklyUsed(int $userId): int
    {
        $week = now()->format('Y-W');

        return (int) Cache::get("ai:quota:week:{$userId}:{$week}", 0);
    }

    public function resolveLimits($user): array
    {
        $defaults = config('ai.quota.defaults', []);
        $overrides = $this->getOverrides();

        $roles = $user->getRoleNames()->toArray();
        $best = null;

        foreach ($roles as $roleName) {
            $effective = $overrides[$roleName] ?? $defaults[$roleName] ?? null;
            if ($effective) {
                if ($best === null || ($effective['daily'] ?? 0) > ($best['daily'] ?? 0)) {
                    $best = $effective;
                }
            }
        }

        return $best ?? ['daily' => 1, 'weekly' => 3];
    }

    /**
     * Get all current quota overrides from the settings table.
     */
    public function getOverrides(): array
    {
        $stored = Setting::where('key', 'ai.quota.overrides')->value('value');

        return is_array($stored) ? $stored : [];
    }

    /**
     * Save quota overrides for one or more roles.
     */
    public function saveOverrides(array $overrides): void
    {
        Setting::updateOrCreate(
            ['key' => 'ai.quota.overrides'],
            ['value' => $overrides, 'company_id' => null]
        );
        Cache::forget('ai:quota:overrides');
    }

    /**
     * Get the full quota table for all roles (defaults + overrides).
     */
    public function getAllQuotas(): array
    {
        $defaults = config('ai.quota.defaults', []);
        $overrides = $this->getOverrides();
        $result = [];

        foreach ($defaults as $role => $limits) {
            $result[$role] = [
                'daily' => $overrides[$role]['daily'] ?? $limits['daily'],
                'weekly' => $overrides[$role]['weekly'] ?? $limits['weekly'],
                'overridden' => isset($overrides[$role]),
            ];
        }

        return $result;
    }

    protected function computeAlerts(int $dailyUsed, int $dailyLimit, int $weeklyUsed, int $weeklyLimit): array
    {
        $thresholds = config('ai.quota.alert_thresholds', [50, 75, 90, 100]);
        $alerts = [];

        $dailyPct = $dailyLimit > 0 ? ($dailyUsed / $dailyLimit) * 100 : 0;
        $weeklyPct = $weeklyLimit > 0 ? ($weeklyUsed / $weeklyLimit) * 100 : 0;

        foreach ($thresholds as $t) {
            if ($dailyPct >= $t || $weeklyPct >= $t) {
                $alerts[] = $t;
            }
        }

        return $alerts;
    }

    protected function denied(): array
    {
        return [
            'allowed' => false,
            'daily_remaining' => 0,
            'weekly_remaining' => 0,
            'daily_limit' => 0,
            'weekly_limit' => 0,
            'daily_used' => 0,
            'weekly_used' => 0,
            'alerts' => [100],
        ];
    }
}
