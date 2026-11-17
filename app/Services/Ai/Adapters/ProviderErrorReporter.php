<?php

namespace App\Services\Ai\Adapters;

use App\Services\Ai\AiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

/**
 * Shared, redacted diagnostics for provider HTTP failures.
 *
 * Provider error bodies explain stale model IDs, retired endpoints and quota
 * problems, but must never leak an API key into logs. Every value that looks
 * like a key is removed before the short summary is written.
 */
trait ProviderErrorReporter
{
    protected function failWithProviderResponse(
        string $label,
        string $provider,
        Response $response,
        string $apiKey = '',
    ): never {
        Log::warning($label.' returned an error response', [
            'provider' => $provider,
            'status' => $response->status(),
            'error' => $this->redactProviderError($response->body(), $apiKey),
        ]);

        throw new AiException("AI provider [{$provider}] returned HTTP {$response->status()}.");
    }

    protected function redactProviderError(string $body, string $apiKey = ''): string
    {
        $body = preg_replace('/\s+/', ' ', $body) ?? '';

        if ($apiKey !== '') {
            $body = str_replace($apiKey, '[REDACTED]', $body);
        }

        $body = preg_replace(
            '/\b(?:AIza|nvapi-|sk-ant-|sk-proj-|sk-)[A-Za-z0-9_\-]{8,}/',
            '[REDACTED]',
            $body,
        ) ?? $body;

        return mb_substr($body, 0, 600);
    }
}
