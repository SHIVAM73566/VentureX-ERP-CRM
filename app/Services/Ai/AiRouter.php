<?php

namespace App\Services\Ai;

use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;

/**
 * Selects exactly ONE provider for a given task.
 *
 * Candidate order: explicit override -> company AI settings -> task routing ->
 * default provider, followed by the configured fallback order. Only providers
 * with a configured API key are considered; the first matching candidate wins.
 *
 * Two guards apply:
 *   - Claude is only ever a candidate when the AI Decision Engine says the task
 *     justifies high-value reasoning (complex task + sufficient impact score).
 *   - Unhealthy providers (repeated recent failures) are temporarily skipped.
 */
class AiRouter
{
    public function __construct(protected AiDecisionEngine $engine) {}

    public function availableProviders(): array
    {
        $available = [];

        foreach (array_keys(config('ai.providers', [])) as $provider) {
            if ($this->isConfigured($provider)) {
                $available[] = $provider;
            }
        }

        return $available;
    }

    public function isConfigured(string $provider): bool
    {
        $config = (array) (config("ai.providers.{$provider}") ?? []);

        // Providers explicitly disabled are never considered
        if (isset($config['enabled']) && $config['enabled'] === false) {
            return false;
        }

        return isset($config['api_key']) && $config['api_key'] !== '';
    }

    public function isEnabled(): bool
    {
        return $this->availableProviders() !== [];
    }

    public function isHealthy(string $provider): bool
    {
        $failures = (int) Cache::get('ai:health:'.$provider, 0);

        return $failures < (int) config('ai.health.max_failures', 3);
    }

    public function healthFail(string $provider): void
    {
        $key = 'ai:health:'.$provider;
        $window = (int) config('ai.health.window_seconds', 600);
        $failures = (int) Cache::get($key, 0);

        Cache::put($key, $failures + 1, $window);
    }

    public function healthReset(string $provider): void
    {
        Cache::forget('ai:health:'.$provider);
    }

    /**
     * @return array{provider: string, model: string}
     */
    public function resolve(string $task, ?string $override = null, ?string $model = null): array
    {
        $settings = app(SettingService::class);
        $companyProvider = trim((string) $settings->get('ai.provider', ''));
        $companyModel = trim((string) $settings->get('ai.model', ''));

        $routing = (array) config('ai.task_routing', []);
        $default = (string) config('ai.default_provider', 'nvidia');

        $candidates = [];

        $effectiveOverride = $override ?? (($companyProvider !== '' && $this->isConfigured($companyProvider))
            ? $companyProvider
            : null);

        if ($effectiveOverride !== null) {
            $candidates[] = $effectiveOverride;
        }

        $mapped = $routing[$task] ?? $default;

        // New format: ['providers' => [...], 'fallback' => '...']
        if (is_array($mapped) && isset($mapped['providers'])) {
            foreach ((array) $mapped['providers'] as $p) {
                $candidates[] = $p;
            }
        } elseif (is_string($mapped)) {
            $candidates[] = $mapped;
        }

        $candidates[] = $default;

        foreach ((array) config('ai.fallback_order', []) as $fallback) {
            $candidates[] = $fallback;
        }

        $unique = [];
        foreach ($candidates as $candidate) {
            if (! in_array($candidate, $unique, true)) {
                $unique[] = $candidate;
            }
        }

        foreach ($unique as $candidate) {
            if ($candidate === 'claude' && ! $this->engine->shouldUseClaude($task)) {
                continue;
            }

            if (! $this->isConfigured($candidate) || ! $this->isHealthy($candidate)) {
                continue;
            }

            $config = (array) (config("ai.providers.{$candidate}") ?? []);

            $resolvedModel = $model !== null && $model !== ''
                ? $model
                : ($companyModel !== '' ? $companyModel : null);

            return [
                'provider' => $candidate,
                'model' => $resolvedModel ?? (string) ($config['model'] ?? config('ai.model')),
            ];
        }

        $provider = $default;

        return [
            'provider' => $provider,
            'model' => $model ?? (string) (config("ai.providers.{$provider}.model") ?? config('ai.model')),
        ];
    }
}
