<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingEvent;
use App\Services\Billing\EntitlementActivationService;
use App\Services\Billing\StripeSignatureVerifier;
use Carbon\CarbonImmutable;
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

        [$claim, $created] = $this->claimEvent($eventId, $eventType, $event);

        // A claim that already exists as processed OR processing belongs to a
        // concurrent/duplicate Stripe delivery. Accept it without applying
        // effects twice. Failed events stay eligible for a Stripe retry.
        if (! $created && in_array($claim->status, ['processed', 'processing'], true)) {
            return response('OK', 200);
        }

        try {
            $this->process($eventType, $event, $eventId);
        } catch (Throwable $e) {
            $claim->forceFill([
                'status' => 'failed',
                'error' => mb_substr($e->getMessage(), 0, 500),
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
            'processed_at' => now(),
        ])->save();

        return response('OK', 200);
    }

    /**
     * @return array{BillingEvent, bool} the ledger row and whether this
     *                                   request created the claim
     */
    protected function claimEvent(string $eventId, string $eventType, array $event): array
    {
        try {
            $claim = BillingEvent::query()->firstOrCreate([
                'event_id' => $eventId,
            ], [
                'event_type' => $eventType,
                'status' => 'processing',
                'payload' => $event,
            ]);

            return [$claim, $claim->wasRecentlyCreated];
        } catch (QueryException) {
            // Concurrent delivery: use the row written by the first request.
            return [BillingEvent::query()->where('event_id', $eventId)->firstOrFail(), false];
        }
    }

    protected function process(string $eventType, array $event, string $eventId): void
    {
        $object = (array) data_get($event, 'data.object', []);

        switch ($eventType) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                $subscriptionId = $this->stringId(data_get($object, 'subscription'));
                if ($subscriptionId === '' && data_get($object, 'payment_status') !== 'paid') {
                    return;
                }
                $this->applyFromTarget(
                    $object,
                    'active',
                    $eventId,
                    $eventType,
                    $subscriptionId,
                    $this->oneTimeExpiry($object, $subscriptionId),
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
                );
                break;

            case 'customer.subscription.updated':
                $status = (string) data_get($object, 'status', '');
                if ($status === 'canceled' || $status === 'incomplete_expired') {
                    $this->revokeFromTarget($object, $eventId, $eventType);
                    break;
                }
                if (! in_array($status, ['active', 'trialing', 'past_due', 'unpaid', 'incomplete'], true)) {
                    return;
                }
                $this->applyFromTarget(
                    $object,
                    in_array($status, ['active', 'trialing'], true) ? 'active' : 'grace',
                    $eventId,
                    $eventType,
                    $this->stringId(data_get($object, 'id')),
                    $this->periodEnd(data_get($object, 'current_period_end')),
                );
                break;

            case 'customer.subscription.deleted':
                $this->revokeFromTarget($object, $eventId, $eventType);
                break;

            default:
                // Unknown events are recorded for idempotency but change nothing.
                Log::info('stripe.webhook.ignored', ['event_type' => $eventType]);
        }
    }

    /**
     * Resolve the tenant/add-on pair from signed event metadata, falling back
     * to the entitlement bound to the Stripe subscription id.
     */
    protected function target(array $object, string $subscriptionId): ?array
    {
        $entitlement = $subscriptionId !== ''
            ? $this->entitlements->findBySubscription($subscriptionId)
            : null;

        $companyId = data_get($object, 'metadata.company_id')
            ?? data_get($object, 'client_reference_id');
        $layerKey = data_get($object, 'metadata.layer_key');

        if (! is_scalar($companyId) || ! ctype_digit((string) $companyId)) {
            $companyId = $entitlement?->company_id;
        }
        if (! is_string($layerKey) || ! is_array(config('layers.layers.'.$layerKey))) {
            $layerKey = $entitlement?->layer_key;
        }

        if (! is_scalar($companyId) || ! ctype_digit((string) $companyId) || ! is_string($layerKey)) {
            Log::warning('stripe.webhook.unattributed', ['event_type' => data_get($object, 'object')]);

            return null;
        }

        return [
            'company_id' => (int) $companyId,
            'layer_key' => $layerKey,
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
                'subscription_id' => $subscriptionId,
                'invoice_id' => $this->stringId(data_get($object, 'id')),
                'customer_id' => $target['customer_id'],
                'lookup_key' => $target['lookup_key'],
            ],
        );
    }

    protected function revokeFromTarget(array $object, string $eventId, string $eventType): void
    {
        $subscriptionId = $this->stringId(data_get($object, 'id'));
        $target = $this->target($object, $subscriptionId);
        if ($target === null) {
            return;
        }

        $this->entitlements->revoke($target['company_id'], $target['layer_key'], [
            'event_id' => $eventId,
            'event_type' => $eventType,
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

    protected function stringId(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value['id'] ?? null;
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
