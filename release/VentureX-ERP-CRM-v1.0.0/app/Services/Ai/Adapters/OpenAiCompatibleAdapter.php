<?php

namespace App\Services\Ai\Adapters;

use App\Services\Ai\AiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OpenAI-compatible chat-completions adapter.
 *
 * Handles both RapidAPI providers (x-rapidapi-key + x-rapidapi-host) and
 * bearer-token providers (NVIDIA NIM, OpenAI, Anthropic). Provider contracts
 * are fully config-driven so endpoint/model changes require no code edits.
 */
class OpenAiCompatibleAdapter implements ProviderAdapter
{
    public function generate(string $provider, array $payload): array
    {
        $config = (array) (config("ai.providers.{$provider}") ?? []);

        if (empty($config['api_key'])) {
            throw new AiException("AI provider [{$provider}] is not configured.");
        }

        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $path = (string) ($config['path'] ?? '/chat/completions');
        $authMode = (string) ($config['auth_mode'] ?? 'bearer');

        if ($baseUrl === '') {
            $host = (string) ($config['host'] ?? '');
            $baseUrl = 'https://'.$host;
        }

        // Prevent double-path: only append if base_url doesn't already end with the path.
        if ($baseUrl !== '' && $path !== '/' && str_ends_with($baseUrl, $path)) {
            $url = $baseUrl;
        } else {
            $url = $baseUrl.$path;
        }

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
                    'x-rapidapi-host' => $config['host'] ?? parse_url($baseUrl, PHP_URL_HOST),
                ]);
            } else {
                $http = $http->withToken($config['api_key']);
            }

            $response = $http->asJson()->post($url, $body);

            if (! $response->successful()) {
                Log::warning('AI provider returned an error response', [
                    'provider' => $provider,
                    'status' => $response->status(),
                ]);

                throw new AiException(
                    "AI provider [{$provider}] returned HTTP {$response->status()}."
                );
            }

            $data = $response->json();

            if ($provider === 'anthropic') {
                $content = $this->anthropicContent($data);
                $usage = $data['usage'] ?? [];
            } else {
                $msg = $data['choices'][0]['message'] ?? [];
                $content = $msg['content'] ?? null;

                // Some reasoning models (e.g. NVIDIA Nemotron) put the output in
                // "reasoning_content" or "reasoning" while leaving "content" null.
                if (! is_string($content) || $content === '') {
                    $content = $msg['reasoning_content'] ?? $msg['reasoning'] ?? null;
                }

                $usage = $data['usage'] ?? [];
            }

            if (! is_string($content) || $content === '') {
                throw new AiException("AI provider [{$provider}] returned an empty response.");
            }

            return [
                'provider' => $provider,
                'model' => $data['model'] ?? $payload['model'],
                'content' => $content,
                'prompt_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
                'latency_ms' => (int) round((microtime(true) - $started) * 1000),
            ];
        } catch (AiException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('AI provider request failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            throw new AiException("AI provider [{$provider}] is temporarily unavailable.");
        }
    }

    public function supports(string $provider): bool
    {
        $config = (array) (config("ai.providers.{$provider}") ?? []);

        return isset($config['base_url']) || isset($config['host']);
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
