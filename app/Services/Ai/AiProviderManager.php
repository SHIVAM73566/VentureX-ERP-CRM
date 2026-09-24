<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Central orchestration for database-backed AI providers.
 *
 * API keys are stored symmetrically-encrypted in the ai_providers table
 * (decrypted only on the server for the current request) and are merged into
 * the runtime provider config so the existing AiRouter + adapters keep working
 * unchanged for every deployment that has not configured providers in the UI.
 *
 * The company is always derived from the authenticated request (CompanyContext),
 * never from the request body, which prevents cross-tenant credential access.
 */
class AiProviderManager
{
    protected array $synced = [];

    public function __construct(
        protected AiCredentialService $credentials,
        protected AiUsageService $usage,
    ) {}

    /**
     * Load the current company's providers and merge them into runtime config
     * (api_key, model, base_url, path, auth_mode). Idempotent per request.
     */
    public function syncForCurrentContext(): void
    {
        $companyId = CompanyContext::id();
        if ($companyId === null) {
            return;
        }

        if (isset($this->synced[$companyId])) {
            return;
        }
        $this->synced[$companyId] = true;

        $providers = AiProvider::query()
            ->where('company_id', $companyId)
            ->get();

        foreach ($providers as $provider) {
            $this->mergeProviderConfig($provider);
        }
    }

    /**
     * Merge one DB provider row into runtime config. Never exposes the key.
     */
    protected function mergeProviderConfig(AiProvider $provider): void
    {
        if (! $provider->enabled) {
            Config::set("ai.providers.{$provider->provider}.enabled", false);

            return;
        }

        $apiKey = $provider->getDecryptedKey();
        if ($apiKey === null || $apiKey === '') {
            return;
        }

        Config::set("ai.providers.{$provider->provider}.api_key", $apiKey);
        Config::set("ai.providers.{$provider->provider}.enabled", true);

        if ($provider->model !== null && $provider->model !== '') {
            Config::set("ai.providers.{$provider->provider}.model", $provider->model);
        }
        if ($provider->base_url !== null && $provider->base_url !== '') {
            Config::set("ai.providers.{$provider->provider}.base_url", $provider->base_url);
        }
        if ($provider->path !== null && $provider->path !== '') {
            Config::set("ai.providers.{$provider->provider}.path", $provider->path);
        }
        if ($provider->auth_mode !== null && $provider->auth_mode !== '') {
            Config::set("ai.providers.{$provider->provider}.auth_mode", $provider->auth_mode);
        }
    }

    /**
     * Providers the current company has stored (regardless of env).
     */
    public function storedProviders(?int $companyId = null): Collection
    {
        $companyId ??= CompanyContext::id();

        if ($companyId === null) {
            return new Collection;
        }

        return AiProvider::query()->where('company_id', $companyId)->get()->keyBy('provider');
    }

    public function isConfiguredViaDb(string $provider, ?int $companyId = null): bool
    {
        $row = $this->storedProviders($companyId)->get($provider);

        if ($row === null || ! $row->enabled) {
            return false;
        }

        $key = $row->getDecryptedKey();

        return $key !== null && $key !== '';
    }

    public function encryptedKeyFor(string $provider, ?int $companyId = null): ?string
    {
        return $this->storedProviders($companyId)->get($provider)?->encrypted_key;
    }
}
