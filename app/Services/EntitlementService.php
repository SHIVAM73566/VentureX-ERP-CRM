<?php

namespace App\Services;

use App\Models\LayerEntitlement;

class EntitlementService
{
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

        $entitlement = LayerEntitlement::query()
            ->where('company_id', $companyId)
            ->where('layer_key', $layerKey)
            ->whereIn('status', ['active', 'grace'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->latest('id')
            ->first();

        return $entitlement !== null;
    }

    /**
     * Get layer config array or null.
     */
    public function configFor(string $layerKey): ?array
    {
        return config("layers.layers.{$layerKey}");
    }
}
