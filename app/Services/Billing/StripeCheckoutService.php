<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Creates Stripe Checkout sessions using server-side configuration only.
 * No keys, price IDs or payment URLs are ever rendered into Blade or JS.
 */
class StripeCheckoutService
{
    /**
     * @return array{id: string, url: string}
     */
    public function createSession(int $companyId, string $layerKey, ?string $email = null): array
    {
        $secret = (string) config('services.stripe.secret');
        $priceId = (string) (config('services.stripe.prices.'.$layerKey) ?? '');
        $layer = config('layers.layers.'.$layerKey);

        if ($secret === '' || $priceId === '' || ! is_array($layer)) {
            throw new RuntimeException('Checkout is not configured for this add-on.');
        }
        if (($layer['status'] ?? '') !== 'available') {
            throw new RuntimeException('This add-on is not available for checkout.');
        }
        if (app(\App\Services\EntitlementService::class)->has($layerKey, $companyId)) {
            throw new RuntimeException('This add-on is already active on your workspace.');
        }

        $mode = in_array($layer['type'] ?? '', ['monthly', 'annual'], true) ? 'subscription' : 'payment';

        $payload = [
            'mode' => $mode,
            'client_reference_id' => (string) $companyId,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'metadata' => [
                'company_id' => (string) $companyId,
                'layer_key' => $layerKey,
                'lookup_key' => (string) ($layer['stripe_lookup_key'] ?? ''),
                'validity_days' => (string) (int) ($layer['validity_days'] ?? 0),
            ],
            'success_url' => $this->url('services.stripe.success_url', route('admin.settings.index', ['tab' => 'plan'])),
            'cancel_url' => $this->url('services.stripe.cancel_url', route('admin.settings.index', ['tab' => 'plan'])),
        ];

        if ($mode === 'subscription') {
            // Subscription metadata is echoed on every invoice/subscription
            // event, so entitlements stay attributable to the right tenant.
            $payload['subscription_data']['metadata'] = [
                'company_id' => (string) $companyId,
                'layer_key' => $layerKey,
                'lookup_key' => (string) ($layer['stripe_lookup_key'] ?? ''),
            ];
        }

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $payload['customer_email'] = $email;
        }

        // A stable per-day key prevents duplicate sessions from double-clicks.
        $idempotencyKey = hash('sha256', implode('|', [
            'checkout', $companyId, $layerKey, now()->toDateString(),
        ]));

        $response = Http::timeout(20)
            ->retry(2, 200)
            ->withToken($secret)
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->asForm()
            ->post($this->endpoint('/v1/checkout/sessions'), $payload);

        if (! $response->successful()) {
            // Log status only: the body can echo request metadata or keys.
            logger()->error('stripe.checkout.failed', [
                'company_id' => $companyId,
                'layer_key' => $layerKey,
                'status' => $response->status(),
            ]);

            throw new RuntimeException('Stripe checkout is temporarily unavailable.');
        }

        $sessionId = $response->json('id');
        $url = $response->json('url');

        if (! is_string($sessionId) || ! is_string($url) || ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('Stripe checkout returned an invalid session.');
        }

        return ['id' => $sessionId, 'url' => $url];
    }

    protected function endpoint(string $path): string
    {
        return (string) config('services.stripe.api_base', 'https://api.stripe.com').$path;
    }

    protected function url(string $configKey, string $fallbackRoute): string
    {
        $configured = trim((string) config($configKey));

        return $configured !== '' ? $configured : $fallbackRoute;
    }
}
