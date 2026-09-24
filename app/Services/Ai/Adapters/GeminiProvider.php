<?php

namespace App\Services\Ai\Adapters;

use App\Services\Ai\AiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Official Google Gemini API adapter (generativelanguage.googleapis.com).
 *
 * Uses the current generateContent REST contract:
 *   POST /v1beta/models/{model}:generateContent
 * Auth via the x-goog-api-key header — the key never appears in a URL or log.
 */
class GeminiProvider implements ProviderAdapter
{
    public function generate(string $provider, array $payload): array
    {
        $config = (array) (config("ai.providers.{$provider}") ?? []);

        if (empty($config['api_key'])) {
            throw new AiException("AI provider [{$provider}] is not configured.");
        }

        $baseUrl = rtrim((string) ($config['base_url']
            ?? 'https://generativelanguage.googleapis.com'), '/');
        $model = (string) ($payload['model'] ?? $config['model'] ?? 'gemini-2.5-flash');
        $url = $baseUrl.'/v1beta/models/'.$model.':generateContent';

        $body = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $payload['user']]]],
            ],
            'systemInstruction' => ['parts' => [['text' => $payload['system'] ?? '']]],
            'generationConfig' => [
                'temperature' => (float) $payload['temperature'],
                'maxOutputTokens' => (int) $payload['max_tokens'],
            ],
        ];

        $started = microtime(true);

        try {
            $response = Http::timeout((int) config('ai.request_timeout', 60))
                ->withHeaders(['x-goog-api-key' => (string) $config['api_key']])
                ->asJson()
                ->post($url, $body);

            if (! $response->successful()) {
                Log::warning('Gemini returned an error response', [
                    'provider' => $provider,
                    'status' => $response->status(),
                ]);

                throw new AiException("AI provider [{$provider}] returned HTTP {$response->status()}.");
            }

            $data = $response->json();
            $candidate = $data['candidates'][0] ?? null;

            $content = null;
            if (is_array($candidate)) {
                $parts = $candidate['content']['parts'] ?? [];
                $content = collect($parts)
                    ->filter(fn ($p) => isset($p['text']))
                    ->pluck('text')
                    ->implode("\n");
            }

            if (! is_string($content) || $content === '') {
                throw new AiException("AI provider [{$provider}] returned an empty response.");
            }

            $usage = $data['usageMetadata'] ?? [];

            return [
                'provider' => $provider,
                'model' => $data['modelVersion'] ?? $model,
                'content' => $content,
                'prompt_tokens' => (int) ($usage['promptTokenCount'] ?? 0),
                'completion_tokens' => (int) ($usage['candidatesTokenCount'] ?? $usage['completionTokenCount'] ?? 0),
                'latency_ms' => (int) round((microtime(true) - $started) * 1000),
            ];
        } catch (AiException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Gemini request failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            throw new AiException("AI provider [{$provider}] is temporarily unavailable.");
        }
    }

    public function supports(string $provider): bool
    {
        return $provider === 'gemini';
    }
}
