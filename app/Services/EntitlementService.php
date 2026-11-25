<?php

namespace App\Services;

use App\Models\LayerEntitlement;
use App\Models\LicenseToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EntitlementService
{
    /**
     * Active layer keys for a company. A drifted database (migrations not yet
     * applied) must not turn the settings page into a 500 — report the issue
     * and fail closed to "no paid layers" instead.
     *
     * @return array<int, string>
     */
    public function activeLayerKeys(?int $companyId = null): array
    {
        $companyId = $companyId ?? CompanyContext::id();
        if ($companyId === null || ! $this->entitlementTableExists()) {
            return [];
        }

        return LayerEntitlement::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['active', 'grace'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->pluck('layer_key')
            ->all();
    }

    /**
     * Check if the given layer is entitled for the company (or current context).
     */
    public function has(string $layerKey, ?int $companyId = null): bool
    {
        $layers = config('layers.layers', []);
        if (! array_key_exists($layerKey, $layers)) {
            return false;
        }

        $companyId = $companyId ?? CompanyContext::id();
        if ($companyId === null) {
            return false;
        }

        // 1) Check synced DB entitlements
        if (! $this->entitlementTableExists()) {
            return false;
        }

        $entitlement = LayerEntitlement::query()
            ->where('company_id', $companyId)
            ->where('layer_key', $layerKey)
            ->whereIn('status', ['active', 'grace'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->latest('id')
            ->first();

        if ($entitlement !== null) {
            return true;
        }

        // 2) Check cached verified license token payload (offline grace aware)
        $tokenRow = LicenseToken::query()
            ->where('company_id', $companyId)
            ->first();

        if ($tokenRow && $tokenRow->payload) {
            $graceOk = $tokenRow->grace_expires_at && $tokenRow->grace_expires_at->gte(now());
            $notExpired = ! $tokenRow->expires_at || $tokenRow->expires_at->gte(now()) || $graceOk;
            if ($notExpired) {
                $ents = data_get($tokenRow->payload, 'entitlements', []);
                $active = data_get($ents, $layerKey.'.active', false);
                if ($active === true || $active === 1 || $active === '1') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get layer config array or null.
     */
    public function configFor(string $layerKey): ?array
    {
        return config("layers.layers.{$layerKey}");
    }

    protected function entitlementTableExists(): bool
    {
        if (app()->runningUnitTests() && ! Schema::hasTable('layer_entitlements')) {
            // Tests intentionally dropping the table should stay quiet.
            return false;
        }

        if (Schema::hasTable('layer_entitlements')) {
            return true;
        }

        Log::warning('entitlements.table_missing', [
            'hint' => 'Run php artisan migrate --force before using the settings page.',
        ]);

        return false;
    }
}
