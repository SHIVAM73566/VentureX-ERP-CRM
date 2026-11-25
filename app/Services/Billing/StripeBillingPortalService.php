<?php

namespace App\Services\Billing;

use App\Models\LayerEntitlement;
use App\Support\StripeUrlGuard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Creates Stripe-hosted Billing Portal sessions from server-side state only.
 * The Stripe customer id is read from a verified entitlement, never from the
 * request, Blade, JavaScript or the URL.
 */
class StripeBillingPortalService
{
    public function hasCustomer(int $companyId): bool
    {
        if (! Schema::hasTable('layer_entitlements')) {
            return false;
        }

        return LayerEntitlement::query()
            ->where('company_id', $companyId)
            ->whereNotNull('stripe_customer_id')
            ->where('stripe_customer_id', '!=', '')
            ->exists();
    }

    /**
     * @return array{url: string}
     */
    public function createSession(int $companyId): array
    {
        $secret = (string) config('services.stripe.secret');
        if ($secret === '') {
            throw new RuntimeException('Billing management is not configured on this server.');
        }

        $customerId = Schema::hasTable('layer_entitlements')
            ? LayerEntitlement::query()
                ->where('company_id', $companyId)
                ->whereNotNull('stripe_customer_id')
                ->where('stripe_customer_id', '!=', '')
                ->orderByDesc('id')
                ->value('stripe_customer_id')
            : null;

        if (! is_string($customerId) || $customerId === '') {
            throw new RuntimeException('Complete a Stripe upgrade first to enable self-service billing.');
        }

        $returnUrl = StripeUrlGuard::returnUrl(
            (string) config('services.stripe.portal_return_url'),
            route('admin.settings.index', ['tab' => 'plan'])
        );

        $response = Http::timeout(20)
            ->retry(2, 200)
            ->withToken($secret)
            ->asForm()
            ->post($this->endpoint('/v1/billing_portal/sessions'), [
                'customer' => $customerId,
                'return_url' => $returnUrl,
            ]);

        if (! $response->successful()) {
            logger()->error('stripe.portal.failed', [
                'company_id' => $companyId,
                'status' => $response->status(),
            ]);

            throw new RuntimeException('Stripe billing management is temporarily unavailable.');
        }

        $url = $response->json('url');
        if (! is_string($url)) {
            throw new RuntimeException('Stripe billing portal returned an invalid session.');
        }

        return ['url' => StripeUrlGuard::sessionUrl($url)];
    }

    protected function endpoint(string $path): string
    {
        return StripeUrlGuard::apiBase().$path;
    }
}
