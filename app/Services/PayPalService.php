<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalService
{
    private string $clientId;

    private string $clientSecret;

    private string $mode;

    private string $webhookId;

    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('paypal.client_id');
        $this->clientSecret = config('paypal.client_secret');
        $this->mode = config('paypal.mode', 'sandbox');
        $this->webhookId = config('paypal.webhook_id');
        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get OAuth access token from PayPal.
     */
    public function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            Log::error('PayPal access token failed', ['status' => $response->status()]);
            throw new RuntimeException('Failed to obtain PayPal access token');
        }

        return $response->json('access_token');
    }

    /**
     * Create a PayPal order for a standard plan purchase.
     * Server-side price calculation — never trusts client input.
     */
    public function createOrder(string $planKey, int $userId): array
    {
        $plan = config("paypal.plans.{$planKey}");
        if (! $plan) {
            throw new RuntimeException("Invalid plan: {$planKey}");
        }

        if ($plan['price_cents'] <= 0) {
            throw new RuntimeException('Plan price must be greater than zero for standard orders');
        }

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'VentureX-ERP-'.uniqid(),
                    'description' => $plan['description'],
                    'amount' => [
                        'currency_code' => $plan['currency'],
                        'value' => number_format($plan['price_cents'] / 100, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => config('paypal.return_url'),
                'cancel_url' => config('paypal.cancel_url'),
            ],
        ]);

        if ($response->failed()) {
            Log::error('PayPal order creation failed', [
                'user_id' => $userId,
                'plan' => $planKey,
                'status' => $response->status(),
            ]);
            throw new RuntimeException('Failed to create PayPal order');
        }

        $orderData = $response->json();

        // Store pending payment in database
        Payment::create([
            'company_id' => \App\Models\Company::first()->id ?? 1,
            'created_by' => $userId,
            'payment_number' => 'PAY-PPL-'.strtoupper(uniqid()),
            'payment_date' => now()->toDateString(),
            'amount' => $plan['price_cents'] / 100,
            'method' => 'card',
            'reference' => $orderData['id'],
            'status' => 'pending',
            'notes' => $plan['description'] ?? $planKey,
        ]);

        return $orderData;
    }

    /**
     * Create a PayPal order for custom amount (feature request / donation).
     * Amount is validated server-side against min/max limits.
     */
    public function createCustomOrder(int $amountCents, string $description, int $userId): array
    {
        $minAmount = config('paypal.feature_request.min_amount_cents', 1000);
        $maxAmount = config('paypal.feature_request.max_amount_cents', 50000);

        // Server-side validation — never trust client
        if ($amountCents < $minAmount || $amountCents > $maxAmount) {
            throw new RuntimeException("Amount must be between \${$minAmount} and \${$maxAmount}");
        }

        $currency = config('paypal.currency', 'USD');
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'VentureX-ERP-FEATURE-'.uniqid(),
                    'description' => $description,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amountCents / 100, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => config('paypal.return_url'),
                'cancel_url' => config('paypal.cancel_url'),
            ],
        ]);

        if ($response->failed()) {
            Log::error('PayPal custom order failed', ['user_id' => $userId, 'amount' => $amountCents]);
            throw new RuntimeException('Failed to create PayPal order');
        }

        $orderData = $response->json();

        Payment::create([
            'company_id' => \App\Models\Company::first()->id ?? 1,
            'created_by' => $userId,
            'payment_number' => 'PAY-PPL-'.strtoupper(uniqid()),
            'payment_date' => now()->toDateString(),
            'amount' => $amountCents / 100,
            'method' => 'card',
            'reference' => $orderData['id'],
            'status' => 'pending',
            'notes' => $description,
        ]);

        return $orderData;
    }

    /**
     * Capture an approved PayPal order.
     */
    public function captureOrder(string $orderId): array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if ($response->failed()) {
            Log::error('PayPal capture failed', ['order_id' => $orderId, 'status' => $response->status()]);
            throw new RuntimeException('Failed to capture PayPal order');
        }

        return $response->json();
    }

    /**
     * Verify PayPal webhook signature to prevent spoofed notifications.
     * Uses PayPal's HMAC verification.
     */
    public function verifyWebhookSignature(array $headers, string $body): bool
    {
        if (empty($this->webhookId)) {
            Log::warning('PayPal webhook verification skipped — no webhook_id configured');

            return false;
        }

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
            'auth_algo' => $headers['paypal-auth-algo'] ?? '',
            'cert_url' => $headers['paypal-cert-url'] ?? '',
            'cipher' => $headers['paypal-cipher'] ?? '',
            'chain_id' => $headers['paypal-chain-id'] ?? '',
            'headers' => json_encode($headers),
            'webhook_id' => $this->webhookId,
            'body' => $body,
        ]);

        if ($response->failed()) {
            Log::error('PayPal webhook verification API failed', ['status' => $response->status()]);

            return false;
        }

        return $response->json('verification_status') === 'SUCCESS';
    }

    /**
     * Get PayPal client ID for frontend JS SDK.
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Check if PayPal is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }
}
