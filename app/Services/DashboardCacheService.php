<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Short-lived, company-scoped cache for the dashboard's expensive aggregate
 * block. A single stable key is used per company and writes invalidate it.
 */
class DashboardCacheService
{
    public const TTL_SECONDS = 60;

    private const EPOCH_KEY = 'venturex:dashboard:epoch';

    public static function key(?int $companyId = null): string
    {
        $epoch = (int) Cache::get(self::EPOCH_KEY, 0);

        return 'venturex:dashboard:aggregates:'.($companyId ?? 0).':v'.$epoch;
    }

    /**
     * @param  callable(): array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public static function remember(?int $companyId, callable $callback): array
    {
        return (array) Cache::remember(
            self::key($companyId),
            now()->addSeconds(self::TTL_SECONDS),
            $callback
        );
    }

    /**
     * Invalidate one company (or every company when no id is supplied).
     */
    public static function forget(?int $companyId = null): void
    {
        if ($companyId !== null) {
            Cache::forget(self::key($companyId));

            return;
        }

        Cache::forever(self::EPOCH_KEY, (int) Cache::get(self::EPOCH_KEY, 0) + 1);
    }
}
