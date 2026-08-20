<?php

namespace App\Services\Ai\Adapters;

use RuntimeException;

class AdapterFactory
{
    protected const REGISTRY = [
        ClaudeProvider::class,
        OpenAiCompatibleAdapter::class,
    ];

    public static function make(string $provider): ProviderAdapter
    {
        foreach (static::REGISTRY as $adapterClass) {
            $adapter = app($adapterClass);

            if ($adapter->supports($provider)) {
                return $adapter;
            }
        }

        throw new RuntimeException("No AI provider adapter available for [{$provider}].");
    }
}
