<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\LayerEntitlement;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

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

        $eventAt = $this->eventTime($stripe['event_created_at'] ?? null);
        $rows = $this->rows($companyId, $layerKey);

        if ($this->isStale($rows, $eventAt)) {
            Log::warning('billing_entitlement.stale_event_skipped', [
                'company_id' => $companyId,
                'layer_key' => $layerKey,
                'event_type' => (string) ($stripe['event_type'] ?? ''),
            ]);

            return $rows->first();
        }

        // Every entitlement is time-bounded. A missing period end from Stripe
        // must never silently become a permanent paid entitlement.
        $expiresAt ??= CarbonImmutable::now()->addDays(max(1, (int) config('services.stripe.grace_days', 7)));

        $attributes = [
            'status' => $status,
            'expires_at' => $expiresAt,
            'last_event_created_at' => $eventAt,
            'meta' => $this->cleanMeta(array_merge($this->metaOf($rows->first()), $stripe, [
                'event_created_at' => $eventAt?->toIso8601String(),
            ])),
        ];

        foreach (['subscription_id' => 'stripe_subscription_id', 'customer_id' => 'stripe_customer_id'] as $source => $column) {
            $value = trim((string) ($stripe[$source] ?? ''));
            if ($value !== '') {
                $attributes[$column] = $value;
            }
        }

        if ($rows->isEmpty()) {
            $entitlement = LayerEntitlement::query()->create([
                'company_id' => $companyId,
                'layer_key' => $layerKey,
                'starts_at' => now(),
            ] + $attributes);
        } else {
            foreach ($rows as $row) {
                $row->forceFill($attributes + ['starts_at' => $row->starts_at ?? now()])->save();
            }
            $entitlement = $rows->first();
        }

        AuditLogger::log('billing_entitlement_'.$status, 'billing', $entitlement, null, [
            'company_id' => $companyId,
            'layer_key' => $layerKey,
        ]);

        return $entitlement;
    }

    public function revoke(int $companyId, string $layerKey, array $stripe = []): void
    {
        $this->assertTarget($companyId, $layerKey);

        $eventAt = $this->eventTime($stripe['event_created_at'] ?? null);
        $rows = $this->rows($companyId, $layerKey);

        if ($this->isStale($rows, $eventAt)) {
            Log::warning('billing_entitlement.stale_revoke_skipped', [
                'company_id' => $companyId,
                'layer_key' => $layerKey,
                'event_type' => (string) ($stripe['event_type'] ?? ''),
            ]);

            return;
        }

        $revokedAt = now()->toIso8601String();
        $attributes = [
            'status' => 'revoked',
            'expires_at' => now(),
            'last_event_created_at' => $eventAt,
            'meta' => $this->cleanMeta(array_merge($this->metaOf($rows->first()), $stripe, [
                'event_created_at' => $eventAt?->toIso8601String(),
                'revoked_at' => $revokedAt,
            ])),
        ];

        foreach (['subscription_id' => 'stripe_subscription_id', 'customer_id' => 'stripe_customer_id'] as $source => $column) {
            $value = trim((string) ($stripe[$source] ?? ''));
            if ($value !== '') {
                $attributes[$column] = $value;
            }
        }

        if ($rows->isEmpty()) {
            $entitlement = LayerEntitlement::query()->create([
                'company_id' => $companyId,
                'layer_key' => $layerKey,
                'starts_at' => now(),
            ] + $attributes);
        } else {
            foreach ($rows as $row) {
                $row->forceFill($attributes + ['starts_at' => $row->starts_at ?? now()])->save();
            }
            $entitlement = $rows->first();
        }

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

    protected function rows(int $companyId, string $layerKey)
    {
        return LayerEntitlement::query()
            ->where('company_id', $companyId)
            ->where('layer_key', $layerKey)
            ->get();
    }

    /**
     * A late Stripe retry must never undo a newer cancellation or upgrade.
     */
    protected function isStale($rows, ?CarbonImmutable $eventAt): bool
    {
        if ($eventAt === null) {
            return false;
        }

        $latest = 0;
        foreach ($rows as $row) {
            $timestamp = $row->last_event_created_at instanceof DateTimeInterface
                ? $row->last_event_created_at->getTimestamp()
                : 0;
            $latest = max($latest, $timestamp);
        }

        return $latest > 0 && $eventAt->getTimestamp() < $latest;
    }

    protected function eventTime(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value;
        }
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }
        if (is_numeric($value) && (int) $value > 0) {
            return CarbonImmutable::createFromTimestampUTC((int) $value);
        }
        if (is_string($value) && $value !== '') {
            try {
                return CarbonImmutable::parse($value, 'UTC');
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function metaOf(?LayerEntitlement $entitlement): array
    {
        return is_array($entitlement?->meta) ? $entitlement->meta : [];
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
            'event_id', 'event_type', 'event_created_at', 'subscription_id',
            'customer_id', 'invoice_id', 'payment_intent_id', 'lookup_key',
            'revoked_at',
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
