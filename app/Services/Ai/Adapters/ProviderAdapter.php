<?php

namespace App\Services\Ai\Adapters;

use App\Services\Ai\AiException;

interface ProviderAdapter
{
    /**
     * Generate a chat completion.
     *
     * @param  array{system: string, user: string, model: string, temperature: float, max_tokens: int}  $payload
     * @return array{provider: string, model: string, content: string|null, prompt_tokens: int, completion_tokens: int, latency_ms: int}
     *
     * @throws AiException
     */
    public function generate(string $provider, array $payload): array;

    /**
     * Whether this adapter can serve the given provider key.
     */
    public function supports(string $provider): bool;
}
