<?php

namespace App\Services\Billing;

use InvalidArgumentException;

/**
 * Verifies Stripe's raw-payload signature without shipping the Stripe SDK.
 *
 * Stripe-Signature: t=<unix>,v1=<hex>[,v1=<hex>...]
 * The signed message is "<t>.<raw body>", hashed with HMAC-SHA256 using the
 * endpoint's whsec_ secret. A freshness window prevents replayed payloads.
 */
class StripeSignatureVerifier
{
    public function verify(string $rawPayload, ?string $signatureHeader): array
    {
        $secret = (string) config('services.stripe.webhook_secret');
        if ($secret === '') {
            throw new InvalidArgumentException('Stripe webhook is not configured.');
        }

        $parts = [];
        foreach (explode(',', (string) $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, '');
            if ($key === 't') {
                $parts['t'] = $value;
            } elseif ($key === 'v1') {
                $parts['v1'][] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signatures = $parts['v1'] ?? [];

        if ($timestamp === null || ! ctype_digit($timestamp) || $signatures === []) {
            throw new InvalidArgumentException('Malformed Stripe signature header.');
        }

        $tolerance = max(30, (int) config('services.stripe.webhook_tolerance_seconds', 300));
        if (abs(time() - (int) $timestamp) > $tolerance) {
            throw new InvalidArgumentException('Stale Stripe signature.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawPayload, $secret);
        $valid = false;
        foreach ($signatures as $candidate) {
            // hash_equals keeps the comparison timing-safe for every candidate.
            if (hash_equals($expected, (string) $candidate)) {
                $valid = true;
            }
        }

        if (! $valid) {
            throw new InvalidArgumentException('Invalid Stripe signature.');
        }

        $event = json_decode($rawPayload, true);
        if (! is_array($event) || ! is_string(data_get($event, 'id')) || ! is_string(data_get($event, 'type'))) {
            throw new InvalidArgumentException('Invalid Stripe event payload.');
        }

        return $event;
    }
}
