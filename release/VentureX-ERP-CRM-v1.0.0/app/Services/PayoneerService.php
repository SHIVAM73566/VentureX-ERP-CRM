<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayoneerService
{
    private string $clientId;

    private string $clientSecret;

    private string $apiUrl;

    private string $webhookSecret;

    private string $email;

    public function __construct()
    {
        $this->clientId = config('payoneer.client_id', '');
        $this->clientSecret = config('payoneer.client_secret', '');
        $this->apiUrl = config('payoneer.api_url', 'https://api.payoneer.com/v2');
        $this->webhookSecret = config('payoneer.webhook_secret', '');
        $this->email = config('payoneer.email', '');
    }

    /**
     * Get license tier configuration.
     */
    public function getLicense(string $tier): ?array
    {
        return config("payoneer.licenses.{$tier}");
    }

    /**
     * Get all license tiers.
     */
    public function getLicenses(): array
    {
        return config('payoneer.licenses', []);
    }

    /**
     * Create a Payoneer Checkout session via API.
     * Falls back to manual payment flow if API is not configured.
     */
    public function createCheckoutSession(string $email, string $name, string $licenseTier): array
    {
        $license = $this->getLicense($licenseTier);
        if (! $license) {
            throw new RuntimeException("Invalid license tier: {$licenseTier}");
        }

        // If Payoneer API is configured, use the Checkout API
        if ($this->isApiConfigured()) {
            return $this->createApiCheckoutSession($email, $name, $licenseTier, $license);
        }

        // Fallback: Manual payment flow (no API needed)
        return $this->createManualPaymentSession($email, $name, $licenseTier, $license);
    }

    /**
     * Create checkout session via Payoneer Checkout API.
     */
    private function createApiCheckoutSession(string $email, string $name, string $licenseTier, array $license): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->timeout(30)
            ->post("{$this->apiUrl}/checkouts", [
                'mode' => 'payment',
            'amount' => number_format($license['price'] / 100, 2),
                'currency' => $license['currency'],
                'description' => $license['description'],
                'buyer' => [
                    'email' => $email,
                    'name' => $name,
                ],
                'metadata' => [
                    'license_tier' => $licenseTier,
                    'app_name' => config('app.name'),
                ],
                'callback_url' => route('pricing.webhook'),
                'return_url' => route('pricing.success'),
                'cancel_url' => route('pricing.cancel'),
            ]);

        if ($response->failed()) {
            Log::error('Payoneer checkout session creation failed', [
                'email' => $email,
                'tier' => $licenseTier,
                'status' => $response->status(),
                'error' => $response->body(),
            ]);
            throw new RuntimeException('Failed to create Payoneer checkout session');
        }

        $data = $response->json();

        return [
            'type' => 'api',
            'url' => $data['redirect_url'] ?? '',
            'session_id' => $data['id'] ?? '',
            'status' => 'pending',
        ];
    }

    /**
     * Create a manual payment session (no Payoneer API needed).
     * The buyer sends payment directly to your Payoneer email.
     */
    private function createManualPaymentSession(string $email, string $name, string $licenseTier, array $license): array
    {
        $reference = 'VX-'.strtoupper(uniqid());

        return [
            'type' => 'manual',
            'payoneer_email' => $this->email,
            'amount' => $license['price'] / 100,
            'currency' => $license['currency'],
            'reference' => $reference,
            'license_tier' => $licenseTier,
            'license_name' => $license['name'],
            'buyer_email' => $email,
            'buyer_name' => $name,
            'instructions' => $this->getPaymentInstructions($reference, $license),
        ];
    }

    /**
     * Generate payment instructions for manual Payoneer payment.
     */
    private function getPaymentInstructions(string $reference, array $license): string
    {
        $amount = number_format($license['price'] / 100, 2);

        return "Send exactly {$license['currency']} {$amount} via Payoneer to:\n"
            ."- Payoneer Email: {$this->email}\n"
            ."- Reference Code: {$reference}\n"
            ."- License: {$license['name']}\n"
            ."\n"
            ."After payment, your license will be activated within 24 hours.";
    }

    /**
     * Verify a manual payment by reference code.
     * This is called by the admin to confirm receipt of payment.
     */
    public function verifyManualPayment(string $reference): bool
    {
        // In production, this would check your records
        // For now, it returns true if the reference format is valid
        return preg_match('/^VX-[A-Z0-9]{6,}$/', $reference) === 1;
    }

    /**
     * Get OAuth access token from Payoneer.
     */
    private function getAccessToken(): string
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post("{$this->apiUrl}/oauth/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Failed to get Payoneer access token');
        }

        return $response->json('access_token', '');
    }

    /**
     * Verify webhook signature from Payoneer.
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            return true;
        }

        $expected = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Check if Payoneer API is properly configured.
     */
    public function isApiConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    /**
     * Check if Payoneer email is configured for manual payments.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->email) || $this->isApiConfigured();
    }

    /**
     * Get the Payoneer email for display.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get checkout page URL (for API mode).
     */
    public function getCheckoutUrl(string $sessionId): string
    {
        return "{$this->apiUrl}/checkouts/{$sessionId}/redirect";
    }
}
