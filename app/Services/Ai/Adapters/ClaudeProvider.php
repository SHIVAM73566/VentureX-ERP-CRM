<?php

namespace App\Services\Ai\Adapters;

use App\Services\Ai\AiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Claude (Anthropic) specialist provider via RapidAPI.
 *
 * Claude is reserved for high-value complex reasoning. The adapter is
 * config-driven (endpoint, host, path, model, auth mode) so the RapidAPI
 * contract can be adjusted without code changes. Accepts either an
 * OpenAI-style chat-completions response or an Anthropic content-block
 * response so it is resilient to either contract.
 */
class ClaudeProvider implements ProviderAdapter
{
    public function supports(string $provider): bool
    {
        return $provider === 'claude';
    }

    public function generate(string $provider, array $payload): array
    {
        $config = (array) (config("ai.providers.{$provider}") ?? []);

        if (empty($config['api_key'])) {
            throw new AiException("AI provider [{$provider}] is not configured.");
        }

        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $path = (string) ($config['path'] ?? '/');
        $authMode = (string) ($config['auth_mode'] ?? 'rapidapi');

        $body = [
            'model' => $payload['model'],
            'temperature' => (float) $payload['temperature'],
            'max_tokens' => (int) $payload['max_tokens'],
            'messages' => [
                ['role' => 'system', 'content' => $payload['system']],
                ['role' => 'user', 'content' => $payload['user']],
            ],
        ];

        $started = microtime(true);

        try {
            $http = Http::timeout((int) config('ai.request_timeout', 60));

            if ($authMode === 'rapidapi') {
                $http = $http->withHeaders([
                    'x-rapidapi-key' => $config['api_key'],
                    'x-rapidapi-host' => $config['host'] ?? 'claude-3-5-sonnet.p.rapidapi.com',
                ]);
            } else {
                $http = $http->withToken($config['api_key']);
            }

            $response = $http->asJson()->post($baseUrl.$path, $body);

            if (! $response->successful()) {
                Log::warning('Claude provider returned an error response', [
                    'provider' => $provider,
                    'status' => $response->status(),
                ]);

                throw new AiException("AI provider [{$provider}] returned HTTP {$response->status()}.");
            }

            $data = $response->json();

            $content = $data['choices'][0]['message']['content'] ?? $this->anthropicContent($data);
            $usage = $data['usage'] ?? [];

            if (! is_string($content) || $content === '') {
                throw new AiException("AI provider [{$provider}] returned an empty response.");
            }

            return [
                'provider' => $provider,
                'model' => $data['model'] ?? $payload['model'],
                'content' => $content,
                'prompt_tokens' => (int) ($usage['prompt_tokens'] ?? $usage['input_tokens'] ?? 0),
                'completion_tokens' => (int) ($usage['completion_tokens'] ?? $usage['output_tokens'] ?? 0),
                'latency_ms' => (int) round((microtime(true) - $started) * 1000),
            ];
        } catch (AiException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Claude provider request failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            throw new AiException("AI provider [{$provider}] is temporarily unavailable.");
        }
    }

    protected function anthropicContent(array $data): ?string
    {
        $blocks = $data['content'] ?? [];

        return collect($blocks)
            ->filter(fn ($b) => ($b['type'] ?? '') === 'text')
            ->pluck('text')
            ->implode("\n");
    }
}
