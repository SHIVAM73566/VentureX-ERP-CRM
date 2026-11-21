<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    /**
     * Handle Stripe webhooks with signature verification (handled by Cashier/middleware)
     * and idempotency (implement via stripe_events table).
     *
     * TODO:
     * - Ensure Stripe webhook middleware verifies signature using STRIPE_WEBHOOK_SECRET.
     * - Record event id in stripe_events before processing; skip if already processed.
     * - Handle: checkout.session.completed, invoice.paid, invoice.payment_failed,
     *   customer.subscription.updated, customer.subscription.deleted.
     * - Never delete customer data on cancellation; only downgrade/disable entitlements.
     */
    public function handleWebhook(Request $request): Response
    {
        // Example idempotency sketch (implement properly):
        // $payload = $request->getContent();
        // $event = \Stripe\Webhook::constructEvent($payload, $request->header('Stripe-Signature'), config('services.stripe.webhook_secret'));
        // if (StripeEvent::where('event_id', $event->id)->exists()) { return response('OK', 200); }
        // ... process ...
        // StripeEvent::create(['event_id' => $event->id, ...]);

        Log::info('Stripe webhook received (scaffold). Implement idempotency + handlers.');

        return parent::handleWebhook($request);
    }
}
