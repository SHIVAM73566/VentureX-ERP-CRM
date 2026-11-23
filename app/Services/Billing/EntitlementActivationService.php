<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\LayerEntitlement;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use RuntimeException;

/**
 * Applies verified billing events to tenant-scoped entitlements.
 * Cancellation/dispute paths only disable paid features; business data is
 * never deleted and exports remain available.
 */
class EntitlementActivationService
{
    public function activate(
        int $companyId,
        string $layerKey,
        string $status = 'active',
        ?CarbonImmutable $expiresAt = null,
        array $stripe = [],
    ): LayerEntitlement {
        $this->assertTarget($companyId, $layerKey);

        // Subscriptions carry a period end from Stripe. Standalone payments
        // must carry an explicit validity window so credits/add-ons never
        // become permanent entitlements by accident.
        if ($expiresAt === null && empty($stripe['subscription_id'])) {
            $expiresAt = CarbonImmutable::now()->addDays(max(1, (int) config('services.stripe.grace_days', 7)));
        }

        $attributes = [
            'status' => $status,
            'starts_at' => now(),
            'expires_at' => $expiresAt,
            'meta' => $this->cleanMeta($stripe),
        ];

        if (isset($stripe['subscription_id'])) {
            $attributes['stripe_subscription_id'] = (string) $stripe['subscription_id'];
        }
        if (isset($stripe['customer_id'])) {
            $attributes['stripe_customer_id'] = (string) $stripe['customer_id'];
        }

        $entitlement = LayerEntitlement::query()->updateOrCreate(
            ['company_id' => $companyId, 'layer_key' => $layerKey],
            $attributes
        );

        AuditLogger::log('billing_entitlement_'.$status, 'billing', $entitlement, null, [
            'company_id' => $companyId,
            'layer_key' => $layerKey,
        ]);

        return $entitlement;
    }

    public function revoke(int $companyId, string $layerKey, array $stripe = []): void
    {
        $this->assertTarget($companyId, $layerKey);

        $entitlement = LayerEntitlement::query()->updateOrCreate(
            ['company_id' => $companyId, 'layer_key' => $layerKey],
            [
                'status' => 'revoked',
                'expires_at' => now(),
                'meta' => $this->cleanMeta($stripe + ['revoked_at' => now()->toIso8601String()]),
            ] + array_filter([
                'stripe_subscription_id' => isset($stripe['subscription_id']) ? (string) $stripe['subscription_id'] : null,
                'stripe_customer_id' => isset($stripe['customer_id']) ? (string) $stripe['customer_id'] : null,
            ])
        );

        AuditLogger::log('billing_entitlement_revoked', 'billing', $entitlement, null, [
            'company_id' => $companyId,
            'layer_key' => $layerKey,
            'reason' => 'subscription_canceled',
        ]);
    }

    public function findBySubscription(string $subscriptionId): ?LayerEntitlement
    {
        if ($subscriptionId === '') {
            return null;
        }

        return LayerEntitlement::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->latest('id')
            ->first();
    }

    protected function assertTarget(int $companyId, string $layerKey): void
    {
        if (! Company::query()->whereKey($companyId)->exists()) {
            throw new RuntimeException('Billing event references an unknown company.');
        }
        if (! is_array(config('layers.layers.'.$layerKey))) {
            throw new RuntimeException('Billing event references an unknown add-on.');
        }
    }

    /**
     * Only keep non-sensitive Stripe identifiers in the entitlement record.
     */
    protected function cleanMeta(array $meta): array
    {
        $allowed = [
            'event_id', 'event_type', 'subscription_id', 'customer_id',
            'invoice_id', 'payment_intent_id', 'lookup_key', 'revoked_at',
        ];

        $clean = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $meta) && $meta[$key] !== null && $meta[$key] !== '') {
                $clean[$key] = is_scalar($meta[$key]) ? (string) $meta[$key] : null;
            }
        }

        return array_filter($clean, fn ($value) => $value !== null);
    }
}
