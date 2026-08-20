<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;

/**
 * Records gateway metrics in the cache and aggregates them for the AI usage
 * dashboard. All counters are per-day keys so the dashboard shows real data
 * without a dedicated analytics table.
 */
class AiUsageMonitor
{
    protected const TTL = 40 * 86400;

    protected function day(string $offset = 'today'): string
    {
        return now()->modify($offset)->format('Y-m-d');
    }

    protected function key(string $metric, string $day): string
    {
        return "ai:usage:{$day}:{$metric}";
    }

    public function request(string $task): void
    {
        $this->increment('requests');
        $this->increment('task:'.$task);
    }

    public function cacheHit(string $task): void
    {
        $this->increment('cache_hits');
        $this->increment('task:'.$task.':hits');
    }

    public function cacheMiss(string $task): void
    {
        $this->increment('cache_misses');
    }

    public function providerCall(string $provider, string $task): void
    {
        $this->increment('provider:'.$provider);
        $this->increment('task:'.$task.':api');
    }

    public function providerCacheHit(string $provider, string $task): void
    {
        $this->increment('provider:'.$provider.':cache_hits');
        $this->increment('task:'.$task.':hits');
    }

    public function error(string $provider): void
    {
        $this->increment('errors');
        $this->increment('provider:'.$provider.':errors');
    }

    public function fallback(string $from, string $to): void
    {
        $this->increment('fallbacks');
        $this->increment("fallback:{$from}->{$to}");
    }

    public function addLatency(int $ms): void
    {
        $this->incrementBy('latency_total', $ms);
        $this->increment('latency_count');
    }

    public function addCost(float $usd): void
    {
        $this->incrementByFloat('cost_total', $usd);
    }

    public function addCostSaved(float $usd): void
    {
        $this->incrementByFloat('cost_saved', $usd);
    }

    protected function increment(string $metric): void
    {
        Cache::increment($this->key($metric, $this->day()), 1, static::TTL);
    }

    protected function incrementBy(string $metric, int $amount): void
    {
        if (Cache::has($this->key($metric, $this->day()))) {
            Cache::increment($this->key($metric, $this->day()), $amount);
        } else {
            Cache::put($this->key($metric, $this->day()), $amount, static::TTL);
        }
    }

    protected function incrementByFloat(string $metric, float $amount): void
    {
        $key = $this->key($metric, $this->day());
        $current = (float) Cache::get($key, 0.0);
        Cache::put($key, $current + $amount, static::TTL);
    }

    public function flushToday(): void
    {
        foreach (Cache::get($this->key('keys', $this->day()), []) as $key) {
            Cache::forget($key);
        }
        Cache::forget($this->key('keys', $this->day()));
    }

    /**
     * Aggregate usage metrics across the last N days.
     */
    public function stats(int $days = 30): array
    {
        $totals = [
            'requests' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'errors' => 0,
            'fallbacks' => 0,
            'latency_total' => 0,
            'latency_count' => 0,
            'cost_total' => 0.0,
            'cost_saved' => 0.0,
            'providers' => [],
            'provider_details' => [],
            'tasks' => [],
            'task_hits' => [],
        ];

        foreach (range(0, $days - 1) as $offset) {
            $day = $this->day("-{$offset} days");

            foreach ([
                'requests', 'cache_hits', 'cache_misses', 'errors', 'fallbacks',
                'latency_total', 'latency_count',
            ] as $metric) {
                $totals[$metric] += (int) Cache::get($this->key($metric, $day), 0);
            }

            $totals['cost_total'] += (float) Cache::get($this->key('cost_total', $day), 0.0);
            $totals['cost_saved'] += (float) Cache::get($this->key('cost_saved', $day), 0.0);

            foreach (array_keys(config('ai.providers', [])) as $provider) {
                $totals['providers'][$provider] = ($totals['providers'][$provider] ?? 0)
                    + (int) Cache::get($this->key('provider:'.$provider, $day), 0);

                $detail = $totals['provider_details'][$provider] ?? [
                    'api_calls' => 0,
                    'cache_hits' => 0,
                    'errors' => 0,
                ];

                $detail['api_calls'] += (int) Cache::get($this->key('provider:'.$provider, $day), 0);
                $detail['cache_hits'] += (int) Cache::get($this->key('provider:'.$provider.':cache_hits', $day), 0);
                $detail['errors'] += (int) Cache::get($this->key('provider:'.$provider.':errors', $day), 0);

                $totals['provider_details'][$provider] = $detail;
            }

            foreach (array_keys(config('ai.task_routing', [])) as $task) {
                $totals['tasks'][$task] = ($totals['tasks'][$task] ?? 0)
                    + (int) Cache::get($this->key('task:'.$task, $day), 0);
                $totals['task_hits'][$task] = ($totals['task_hits'][$task] ?? 0)
                    + (int) Cache::get($this->key('task:'.$task.':hits', $day), 0);
            }
        }

        $apiCalls = $totals['requests'] - $totals['cache_hits'];

        $providerDetails = [];
        foreach ($totals['provider_details'] as $provider => $detail) {
            $requests = $detail['api_calls'] + $detail['cache_hits'];
            $providerDetails[$provider] = [
                'requests' => $requests,
                'api_calls' => $detail['api_calls'],
                'cache_hits' => $detail['cache_hits'],
                'errors' => $detail['errors'],
                'cache_hit_rate' => $requests > 0
                    ? round($detail['cache_hits'] / $requests * 100, 1)
                    : 0.0,
            ];
        }

        return [
            'requests' => $totals['requests'],
            'api_calls' => max(0, $apiCalls),
            'cache_hits' => $totals['cache_hits'],
            'cache_misses' => $totals['cache_misses'],
            'cache_hit_rate' => $totals['requests'] > 0
                ? round($totals['cache_hits'] / $totals['requests'] * 100, 1)
                : 0.0,
            'errors' => $totals['errors'],
            'fallbacks' => $totals['fallbacks'],
            'avg_latency_ms' => $totals['latency_count'] > 0
                ? (int) round($totals['latency_total'] / $totals['latency_count'])
                : 0,
            'estimated_cost' => round($totals['cost_total'], 4),
            'estimated_savings' => round($totals['cost_saved'], 4),
            'providers' => array_filter($totals['providers'], fn ($n) => $n > 0),
            'provider_details' => array_filter($providerDetails, fn ($d) => $d['requests'] > 0),
            'tasks' => array_filter($totals['tasks'], fn ($n) => $n > 0),
            'task_hits' => array_filter($totals['task_hits'], fn ($n) => $n > 0),
        ];
    }
}
