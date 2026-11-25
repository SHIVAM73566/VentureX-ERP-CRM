<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingEvent;
use App\Services\Billing\EntitlementActivationService;
use App\Services\Billing\StripeSignatureVerifier;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Stripe webhook receiver. CSRF is intentionally exempt for this endpoint;
 * authenticity comes from the verified raw-body Stripe-Signature instead.
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeSignatureVerifier $verifier,
        protected EntitlementActivationService $entitlements,
    ) {}

    public function handle(Request $request): Response
    {
        $raw = $request->getContent();

        try {
            $event = $this->verifier->verify($raw, $request->header('Stripe-Signature'));
        } catch (InvalidArgumentException) {
            // Never log the raw body or signature: they can contain PII.
            Log::warning('stripe.webhook.rejected', ['ip' => $request->ip()]);

            return response('Invalid Stripe signature', 400);
        }

        $eventId = (string) $event['id'];
        $eventType = (string) $event['type'];
        $eventCreatedAt = $this->eventTimestamp(data_get($event, 'created'));

        [$claim, $shouldProcess] = $this->claimEvent($eventId, $eventType, $eventCreatedAt, $event);

        // A fresh claim or a duplicate retry from the same delivery belongs to
        // a concurrent request. Accept it without applying effects twice.
        if (! $shouldProcess) {
            return response('OK', 200);
        }

        try {
            $this->process($eventType, $event, $eventId, $eventCreatedAt);
        } catch (Throwable $e) {
            $claim->forceFill([
                'status' => 'failed',
                'error' => mb_substr($e->getMessage(), 0, 500),
                'claimed_at' => null,
            ])->save();

            Log::error('stripe.webhook.processing_failed', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'message' => $e->getMessage(),
            ]);

            // 5xx makes Stripe retry with the same event id.
            return response('Webhook processing failed', 500);
        }

        $claim->forceFill([
            'status' => 'processed',
            'error' => null,
            'claimed_at' => null,
            'processed_at' => now(),
        ])->save();

        return response('OK', 200);
    }

    /**
     * @return array{BillingEvent, bool} the ledger row and whether this
     *                                   request owns the processing lease
     */
    protected function claimEvent(
        string $eventId,
        string $eventType,
        ?CarbonImmutable $eventCreatedAt,
        array $event,
    ): array {
        try {
            $claim = BillingEvent::query()->firstOrCreate([
                'event_id' => $eventId,
            ], [
                'event_type' => $eventType,
                'event_created_at' => $eventCreatedAt,
                'status' => 'processing',
                'claimed_at' => now(),
                // Store identifiers only, never the full signed payload (PII).
                'payload' => $this->minimalPayload($event),
            ]);
        } catch (QueryException) {
            // Concurrent delivery: use the row written by the first request.
            $claim = BillingEvent::query()->where('event_id', $eventId)->firstOrFail();
        }

        if ($claim->wasRecentlyCreated) {
            return [$claim, true];
        }
        if ($claim->status === 'processed') {
            return [$claim, false];
        }

        $leaseSeconds = max(30, (int) config('services.stripe.claim_lease_seconds', 300));
        $cutoff = CarbonImmutable::now()->subSeconds($leaseSeconds);
        $claimedAt = $claim->claimed_at instanceof DateTimeInterface
            ? CarbonImmutable::instance($claim->claimed_at)
            : null;

        if ($claim->status === 'processing' && $claimedAt !== null && $claimedAt->gt($cutoff)) {
            return [$claim, false];
        }
        if (! in_array($claim->status, ['processing', 'failed'], true)) {
            return [$claim, false];
        }

        // A crashed worker must not leave the event wedged as "processing"
        // forever. This conditional update gives this request the lease only
        // if no other worker owns it.
        $reclaimed = BillingEvent::query()
            ->whereKey($claim->id)
            ->whereIn('status', ['processing', 'failed'])
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('claimed_at')->orWhere('claimed_at', '<=', $cutoff);
            })
            ->update([
                'status' => 'processing',
                'claimed_at' => now(),
                'error' => null,
            ]);

        $claim->refresh();

        return [$claim, $reclaimed === 1];
    }

    protected function minimalPayload(array $event): array
    {
        $object = (array) data_get($event, 'data.object', []);

        return [
            'event' => [
                'id' => (string) ($event['id'] ?? ''),
                'type' => (string) ($event['type'] ?? ''),
                'created' => data_get($event, 'created'),
            ],
            'object' => [
                'id' => $this->stringId(data_get($object, 'id')),
                'object' => $this->stringId(data_get($object, 'object')),
            ],
        ];
    }

    protected function process(
        string $eventType,
        array $event,
        string $eventId,
        ?CarbonImmutable $eventCreatedAt = null,
    ): void {
        $object = (array) data_get($event, 'data.object', []);

        switch ($eventType) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                // Unpaid / incomplete checkouts must never grant access.
                if ((string) data_get($object, 'payment_status', '') !== 'paid') {
                    Log::info('stripe.webhook.unpaid_checkout_ignored', ['event_id' => $eventId]);

                    return;
                }

                $subscriptionId = $this->stringId(data_get($object, 'subscription'));
                $this->applyFromTarget(
                    $object,
                    'active',
                    $eventId,
                    $eventType,
                    $subscriptionId,
                    $this->oneTimeExpiry($object, $subscriptionId),
                    $eventCreatedAt,
                );
                break;

            case 'invoice.paid':
                $this->applyFromTarget(
                    $object,
                    'active',
                    $eventId,
                    $eventType,
                    $this->invoiceSubscriptionId($object),
                    $this->periodEnd(data_get($object, 'lines.data.0.current_period_end')),
                    $eventCreatedAt,
                );
                break;

            case 'invoice.payment_failed':
                $this->applyFromTarget(
                    $object,
                    'grace',
                    $eventId,
                    $eventType,
                    $this->invoiceSubscriptionId($object),
                    CarbonImmutable::now()->addDays(max(1, (int) config('services.stripe.grace_days', 7))),
                    $eventCreatedAt,
                );
                break;

            case 'customer.subscription.updated':
                $status = (string) data_get($object, 'status', '');
                if ($status === 'canceled' || $status === 'incomplete_expired') {
                    $this->revokeFromTarget($object, $eventId, $eventType, $eventCreatedAt);
                    break;
                }
                // "incomplete" never proves payment. Do not activate it.
                if (! in_array($status, ['active', 'trialing', 'past_due', 'unpaid'], true)) {
                    return;
                }
                $this->applyFromTarget(
                    $object,
                    in_array($status, ['active', 'trialing'], true) ? 'active' : 'grace',
                    $eventId,
                    $eventType,
                    $this->stringId(data_get($object, 'id')),
                    $this->periodEnd(data_get($object, 'current_period_end')),
                    $eventCreatedAt,
                );
                break;

            case 'customer.subscription.deleted':
                $this->revokeFromTarget($object, $eventId, $eventType, $eventCreatedAt);
                break;

            default:
                // Unknown events are recorded for idempotency but change nothing.
                Log::info('stripe.webhook.ignored', ['event_type' => $eventType]);
        }
    }

    /**
     * Resolve the tenant/add-on pair. A verified subscription binding wins;
     * metadata is used only when there is no binding, and a conflicting
     * metadata claim is rejected instead of redirecting an entitlement.
     */
    protected function target(array $object, string $subscriptionId): ?array
    {
        $binding = $subscriptionId !== ''
            ? $this->entitlements->findBySubscription($subscriptionId)
            : null;

        $companyId = data_get($object, 'metadata.company_id')
            ?? data_get($object, 'client_reference_id');
        $layerKey = data_get($object, 'metadata.layer_key');

        $hasCompany = is_scalar($companyId) && ctype_digit((string) $companyId);
        $hasLayer = is_string($layerKey) && is_array(config('layers.layers.'.$layerKey));

        if ($binding !== null) {
            if ($hasCompany && (int) $companyId !== (int) $binding->company_id) {
                Log::warning('stripe.webhook.metadata_conflict', ['field' => 'company_id']);

                return null;
            }
            if ($hasLayer && $layerKey !== $binding->layer_key) {
                Log::warning('stripe.webhook.metadata_conflict', ['field' => 'layer_key']);

                return null;
            }

            $customerId = $this->stringId(data_get($object, 'customer'));
            if ($customerId === '') {
                $customerId = $this->stringId($binding->stripe_customer_id);
            }

            return [
                'company_id' => (int) $binding->company_id,
                'layer_key' => (string) $binding->layer_key,
                'lookup_key' => (string) (data_get($object, 'metadata.lookup_key') ?? ''),
                'customer_id' => $customerId,
            ];
        }

        if (! $hasCompany || ! $hasLayer) {
            Log::warning('stripe.webhook.unattributed', ['event_type' => data_get($object, 'object')]);

            return null;
        }

        return [
            'company_id' => (int) $companyId,
            'layer_key' => (string) $layerKey,
            'lookup_key' => (string) (data_get($object, 'metadata.lookup_key') ?? ''),
            'customer_id' => $this->stringId(data_get($object, 'customer')),
        ];
    }

    protected function applyFromTarget(
        array $object,
        string $status,
        string $eventId,
        string $eventType,
        string $subscriptionId = '',
        ?CarbonImmutable $expiresAt = null,
        ?CarbonImmutable $eventCreatedAt = null,
    ): void {
        $target = $this->target($object, $subscriptionId);
        if ($target === null) {
            return;
        }

        $this->entitlements->activate(
            $target['company_id'],
            $target['layer_key'],
            $status,
            $expiresAt,
            [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'event_created_at' => $eventCreatedAt?->toIso8601String(),
                'subscription_id' => $subscriptionId,
                'invoice_id' => $this->stringId(data_get($object, 'id')),
                'customer_id' => $target['customer_id'],
                'lookup_key' => $target['lookup_key'],
            ],
        );
    }

    protected function revokeFromTarget(
        array $object,
        string $eventId,
        string $eventType,
        ?CarbonImmutable $eventCreatedAt = null,
    ): void {
        $subscriptionId = $this->stringId(data_get($object, 'id'));
        $target = $this->target($object, $subscriptionId);
        if ($target === null) {
            return;
        }

        $this->entitlements->revoke($target['company_id'], $target['layer_key'], [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'event_created_at' => $eventCreatedAt?->toIso8601String(),
            'subscription_id' => $subscriptionId,
            'customer_id' => $target['customer_id'],
        ]);
    }

    protected function invoiceSubscriptionId(array $object): string
    {
        return $this->stringId(
            data_get($object, 'subscription')
            ?? data_get($object, 'parent.subscription_details.subscription')
            ?? data_get($object, 'lines.data.0.subscription')
        );
    }

    protected function oneTimeExpiry(array $object, string $subscriptionId): ?CarbonImmutable
    {
        if ($subscriptionId !== '') {
            return null;
        }

        $days = (int) data_get($object, 'metadata.validity_days', 0);
        if ($days <= 0) {
            $days = max(1, (int) config('services.stripe.grace_days', 7));
        }

        return CarbonImmutable::now()->addDays($days);
    }

    protected function periodEnd(mixed $timestamp): ?CarbonImmutable
    {
        if (! is_numeric($timestamp) || (int) $timestamp <= 0) {
            return null;
        }

        return CarbonImmutable::createFromTimestampUTC((int) $timestamp);
    }

    protected function eventTimestamp(mixed $value): ?CarbonImmutable
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

    protected function stringId(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value['id'] ?? null;
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
