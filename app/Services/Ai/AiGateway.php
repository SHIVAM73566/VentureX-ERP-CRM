<?php

namespace App\Services\Ai;

use App\Services\Ai\Adapters\AdapterFactory;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central AI Gateway. Every AI request from the CRM/ERP passes through here.
 *
 * Order of operations:
 *   1. Normalize request + build a company/user/task/context scoped cache key.
 *   2. Cache hit -> return immediately (saves tokens + latency).
 *   3. Local intelligence is handled by callers BEFORE reaching the gateway.
 *   4. Deduplicate identical in-flight requests (one API call, many consumers).
 *   5. Route to exactly ONE provider, with fallback only when that fails.
 *   6. Normalize the response, estimate cost, cache it, record usage.
 *
 * The gateway never exposes provider keys, secrets or stack traces.
 */
class AiGateway
{
    public function __construct(
        protected AiRouter $router,
        protected AiUsageMonitor $usage,
        protected AiQuotaService $quota,
    ) {}

    public function isEnabled(): bool
    {
        return $this->router->isEnabled();
    }

    public function availableProviders(): array
    {
        return $this->router->availableProviders();
    }

    /**
     * @param  array{task?: string, provider?: string|null, model?: string|null, temperature?: float|null, max_tokens?: int|null, cache?: bool, ttl?: int, context?: string|null}  $options
     * @return array{content: string, provider: string, model: string, prompt_tokens: int, completion_tokens: int, latency_ms: int, cached: bool, cost: float}
     *
     * @throws AiException
     */
    public function chat(string $system, string $user, array $options = []): array
    {
        if (! $this->isEnabled()) {
            throw new AiException('AI is not configured. Set a provider API key in the environment. See documentation/AI-SETUP.md for details.');
        }

        $task = (string) ($options['task'] ?? 'general_assistant');
        $providerOverride = isset($options['provider']) && $options['provider'] !== null
            ? (string) $options['provider'] : null;
        $modelOverride = isset($options['model']) && $options['model'] !== null
            ? (string) $options['model'] : null;
        $maxTokens = (int) ($options['max_tokens'] ?? config('ai.max_tokens', 2048));
        $temperature = (float) ($options['temperature'] ?? config('ai.temperature', 0.4));
        $context = (string) ($options['context'] ?? '');
        $useCache = (bool) ($options['cache'] ?? true);
        $ttl = (int) ($options['ttl'] ?? config('ai.cache_ttl', 86400));

        $this->usage->request($task);

        if (config('ai.quota.enabled', true)) {
            $quota = $this->quota->check();
            if (! $quota['allowed']) {
                $dailyRemaining = $quota['daily_remaining'] ?? 0;
                $weeklyRemaining = $quota['weekly_remaining'] ?? 0;
                $resetsDaily = now()->endOfDay()->diffForHumans(null, true);
                $resetsWeekly = now()->endOfWeek()->diffForHumans(null, true);
                $msg = 'Your AI quota has been exhausted. ';
                $msg .= "Daily remaining: {$dailyRemaining} (resets in {$resetsDaily}). ";
                $msg .= "Weekly remaining: {$weeklyRemaining} (resets in {$resetsWeekly}). ";
                $msg .= 'Contact your administrator to increase your quota.';
                throw new AiException($msg);
            }
        }

        $cacheKey = $this->cacheKey($task, $system, $user, $providerOverride, $modelOverride, $maxTokens, $context);

        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                $this->usage->cacheHit($task);
                $this->usage->providerCacheHit((string) ($cached['provider'] ?? 'unknown'), $task);
                $this->usage->addCostSaved((float) ($cached['cost'] ?? 0));

                return [...$cached, 'cached' => true];
            }

            $this->usage->cacheMiss($task);
        }

        $resolved = $this->router->resolve($task, $providerOverride, $modelOverride);
        $primary = $resolved['provider'];

        $this->assertRateLimit($primary);

        // Deduplicate concurrent identical requests. Some cache drivers (e.g.
        // file) do not support atomic locks; degrade to no deduplication then.
        $lock = null;
        $acquired = true;

        try {
            $lock = Cache::lock('ai:dedup:'.$cacheKey, (int) config('ai.dedup_lock_ttl', 180));
            $acquired = $lock->get();
        } catch (\InvalidArgumentException) {
            // LockProvider not supported by this cache driver; skip dedup.
            $lock = null;
            $acquired = true;
        }

        if ($lock !== null && ! $acquired) {
            $cached = $this->waitForDuplicate($cacheKey, $task);
            if ($cached !== null) {
                return $cached;
            }

            // The in-flight request likely failed; try to acquire and run.
            try {
                $lock = Cache::lock('ai:dedup:'.$cacheKey, (int) config('ai.dedup_lock_ttl', 180));
                $acquired = $lock->get();
            } catch (\InvalidArgumentException) {
                $lock = null;
                $acquired = true;
            }
        }

        try {
            if ($useCache) {
                $cached = Cache::get($cacheKey);
                if (is_array($cached)) {
                    $this->usage->cacheHit($task);
                    $this->usage->providerCacheHit((string) ($cached['provider'] ?? 'unknown'), $task);

                    return [...$cached, 'cached' => true];
                }
            }

            // Record quota only when a request actually reaches a provider,
            // so cache hits never burn quota.
            if (config('ai.quota.enabled', true)) {
                $this->quota->record(auth()->id());
            }

            $result = $this->callWithFallback($task, $resolved, $system, $user, $temperature, $maxTokens, $providerOverride);

            $payload = [
                'content' => $result['content'],
                'provider' => $result['provider'],
                'model' => $result['model'],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'latency_ms' => $result['latency_ms'],
                'cost' => $result['cost'],
            ];

            if ($useCache) {
                Cache::put($cacheKey, $payload, $ttl);
            }

            $this->usage->addLatency($result['latency_ms']);
            $this->usage->addCost($result['cost']);

            return [...$payload, 'cached' => false];
        } finally {
            if ($acquired && $lock !== null) {
                try {
                    $lock->release();
                } catch (Throwable) {
                    // The lock expires on its own; nothing else to do.
                }
            }
        }
    }

    protected function waitForDuplicate(string $cacheKey, string $task): ?array
    {
        $waited = 0.0;

        while ($waited < 12) {
            usleep(250000);
            $waited += 0.25;

            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                $this->usage->cacheHit($task);
                $this->usage->providerCacheHit((string) ($cached['provider'] ?? 'unknown'), $task);

                return [...$cached, 'cached' => true];
            }
        }

        return null;
    }

    protected function cacheKey(string $task, string $system, string $user, ?string $provider, ?string $model, int $maxTokens, string $context): string
    {
        $companyId = CompanyContext::id() ?? '0';
        $userId = auth()->id() ?? '0';
        $contextHash = sha1(implode('|', [$system, $user, $provider ?? '', $model ?? '', $maxTokens, $context]));

        return "ai:{$companyId}:{$userId}:{$task}:{$contextHash}";
    }

    protected function assertRateLimit(string $provider): void
    {
        $companyId = CompanyContext::id();
        $userId = auth()->id();
        $hour = now()->format('Y-m-d-H');
        $day = now()->format('Y-m-d');

        $userHourLimit = (int) config('ai.rate_limit.max_per_user_per_hour', 60);
        if ($userId && $userHourLimit > 0) {
            $count = (int) Cache::get("ai:rl:user:{$userId}:{$hour}", 0);
            if ($count >= $userHourLimit) {
                throw new AiException('Your hourly AI request limit was reached. Please try again later or request additional access from your administrator.');
            }
            Cache::increment("ai:rl:user:{$userId}:{$hour}", 1, 7200);
        }

        $userDayLimit = (int) config('ai.rate_limit.max_per_user_per_day', 200);
        if ($userId && $userDayLimit > 0) {
            $count = (int) Cache::get("ai:rl:user_day:{$userId}:{$day}", 0);
            if ($count >= $userDayLimit) {
                throw new AiException('Your daily AI request limit was reached. Request additional access from your administrator.');
            }
            Cache::increment("ai:rl:user_day:{$userId}:{$day}", 1, 172800);
        }

        $companyLimit = (int) config('ai.rate_limit.max_per_company_per_hour', 300);
        if ($companyId && $companyLimit > 0) {
            $count = (int) Cache::get("ai:rl:company:{$companyId}:{$hour}", 0);
            if ($count >= $companyLimit) {
                throw new AiException('The hourly AI request limit for your company was reached. Please try again later.');
            }
            Cache::increment("ai:rl:company:{$companyId}:{$hour}", 1, 7200);
        }
    }

    protected function callWithFallback(string $task, array $resolved, string $system, string $user, float $temperature, int $maxTokens, ?string $providerOverride = null): array
    {
        $primary = $resolved['provider'];
        $attempted = [];

        foreach ($this->orderedCandidates($task, $primary, $providerOverride) as $provider) {
            if (in_array($provider, $attempted, true)) {
                continue;
            }
            $attempted[] = $provider;

            try {
                $this->usage->providerCall($provider, $task);

                $started = microtime(true);
                $result = AdapterFactory::make($provider)->generate($provider, [
                    'system' => $system,
                    'user' => $user,
                    'model' => $provider === $primary
                        ? ($resolved['model'] ?? config('ai.model'))
                        : (string) (config("ai.providers.{$provider}.model") ?? config('ai.model')),
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);
                $result['latency_ms'] = (int) round((microtime(true) - $started) * 1000);
                $result['cost'] = $this->estimateCost($provider, $result['prompt_tokens'], $result['completion_tokens']);

                $this->router->healthReset($provider);

                if ($provider !== $primary) {
                    $this->usage->fallback($primary, $provider);
                }

                return $result;
            } catch (AiException $e) {
                $this->usage->error($provider);
                $this->router->healthFail($provider);
                Log::warning('AI provider failed, trying fallback', [
                    'provider' => $provider,
                    'task' => $task,
                ]);
            }
        }

        throw new AiException('AI is temporarily unavailable. Your ERP data and workflows are still working.');
    }

    protected function orderedCandidates(string $task, string $primary, ?string $providerOverride = null): array
    {
        $candidates = [$primary];

        // Task-specific routing takes priority over global fallback_order
        $routing = (array) config('ai.task_routing', []);
        $taskConfig = $routing[$task] ?? null;

        if (is_array($taskConfig) && isset($taskConfig['providers'])) {
            foreach ((array) $taskConfig['providers'] as $provider) {
                if (! in_array($provider, $candidates, true)) {
                    $candidates[] = $provider;
                }
            }
        }

        // If an explicit override was requested, add it early (after task-specific)
        if ($providerOverride !== null && ! in_array($providerOverride, $candidates, true)) {
            $candidates[] = $providerOverride;
        }

        // Global fallback_order as final resort
        foreach ((array) config('ai.fallback_order', []) as $fallback) {
            if (! in_array($fallback, $candidates, true)) {
                $candidates[] = $fallback;
            }
        }

        return array_values(array_filter(
            $candidates,
            fn ($p) => $this->router->isConfigured($p) && $this->router->isHealthy($p)
        ));
    }

    protected function estimateCost(string $provider, int $promptTokens, int $completionTokens): float
    {
        $rates = (array) (config("ai.cost_per_mtok.{$provider}") ?? [0.5, 1.5]);
        $inputRate = (float) ($rates[0] ?? 0.5);
        $outputRate = (float) ($rates[1] ?? 1.5);

        return round(
            ($promptTokens / 1_000_000) * $inputRate + ($completionTokens / 1_000_000) * $outputRate,
            6
        );
    }
}
