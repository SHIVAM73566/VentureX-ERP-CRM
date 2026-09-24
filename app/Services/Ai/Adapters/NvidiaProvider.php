<?php

namespace App\Services\Ai\Adapters;

use App\Services\Ai\AiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * NVIDIA API adapter. Supports both the standard NVIDIA NIM hosted API
 * (integrate.api.nvidia.com, OpenAI-compatible chat completions) and
 * self-hosted / custom NVIDIA endpoints because base_url + path + model are
 * all config-driven. Authentication is via the Authorization: Bearer header;
 * the key never appears in a URL or log.
 */
class NvidiaProvider implements ProviderAdapter
{
    public function generate(string $provider, array $payload): array
    {
        $config = (array) (config("ai.providers.{$provider}") ?? []);

        if (empty($config['api_key'])) {
            throw new AiException("AI provider [{$provider}] is not configured.");
        }

        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://integrate.api.nvidia.com'), '/');
        $path = (string) ($config['path'] ?? '/v1/chat/completions');

        if ($path !== '/' && str_ends_with($baseUrl, $path)) {
            $url = $baseUrl;
        } else {
            $url = $baseUrl.$path;
        }

        $body = [
            'model' => (string) ($payload['model'] ?? $config['model'] ?? 'nvidia/llama-3.3-nemotron-super-49b-v1.5'),
            'temperature' => (float) $payload['temperature'],
            'max_tokens' => (int) $payload['max_tokens'],
            'messages' => [
                ['role' => 'system', 'content' => $payload['system']],
                ['role' => 'user', 'content' => $payload['user']],
            ],
        ];

        $started = microtime(true);

        try {
            $response = Http::timeout((int) config('ai.request_timeout', 60))
                ->withToken((string) $config['api_key'])
                ->asJson()
                ->post($url, $body);

            if (! $response->successful()) {
                Log::warning('NVIDIA returned an error response', [
                    'provider' => $provider,
                    'status' => $response->status(),
                ]);

                throw new AiException("AI provider [{$provider}] returned HTTP {$response->status()}.");
            }

            $data = $response->json();
            $msg = $data['choices'][0]['message'] ?? [];
            $content = $msg['content'] ?? null;

            if (! is_string($content) || $content === '') {
                $content = $msg['reasoning_content'] ?? $msg['reasoning'] ?? null;
            }

            if (! is_string($content) || $content === '') {
                throw new AiException("AI provider [{$provider}] returned an empty response.");
            }

            $usage = $data['usage'] ?? [];

            return [
                'provider' => $provider,
                'model' => $data['model'] ?? $body['model'],
                'content' => $content,
                'prompt_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($usage['completion_tokens'] ?? 0),
                'latency_ms' => (int) round((microtime(true) - $started) * 1000),
            ];
        } catch (AiException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('NVIDIA request failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            throw new AiException("AI provider [{$provider}] is temporarily unavailable.");
        }
    }

    public function supports(string $provider): bool
    {
        return $provider === 'nvidia';
    }
}