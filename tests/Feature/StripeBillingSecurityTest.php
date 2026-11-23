<?php

namespace Tests\Feature;

use App\Models\BillingEvent;
use App\Models\Company;
use App\Models\LayerEntitlement;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StripeBillingSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        $this->company = Company::create(['name' => 'Billing Co', 'email' => 'billing@example.test', 'is_active' => true]);
        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        $this->admin->assignRole('super_admin');

        config([
            'services.stripe.secret' => 'sk_test_not_a_real_key',
            'services.stripe.webhook_secret' => 'whsec_test_secret',
            'services.stripe.api_base' => 'https://api.stripe.com',
            'services.stripe.prices.granular_rbac' => 'price_test_granular',
            'services.stripe.prices.ai_credits' => 'price_test_ai',
        ]);
    }

    protected function adminSession(): array
    {
        return [
            'two_factor_verified_at' => now()->timestamp,
            'step_up_verified_at' => now()->timestamp,
        ];
    }

    protected function signedPost(array $event, bool $valid = true): \Illuminate\Testing\TestResponse
    {
        $raw = json_encode($event, JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = hash_hmac(
            'sha256',
            $timestamp.'.'.$raw,
            $valid ? 'whsec_test_secret' : 'wrong_secret'
        );
        $header = 't='.$timestamp.',v1='.$signature;

        return $this->call(
            'POST',
            route('billing.stripe.webhook'),
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $header, 'CONTENT_TYPE' => 'application/json'],
            $raw
        );
    }

    // ---------------------------------------------------------------- settings

    public function test_settings_pages_return_200_for_authorized_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Settings');

        $this->actingAs($this->admin)
            ->get(route('admin.settings.index', ['tab' => 'plan']))
            ->assertOk()
            ->assertSee('Plan &amp; Layers', false)
            ->assertSee('Granular roles &amp; permissions', false)
            ->assertSee('Industry template: Manufacturing+', false)
            ->assertSee('Advanced logistics suite', false)
            ->assertSee('Custom development credits', false)
            ->assertDontSee('sk_test_not_a_real_key')
            ->assertDontSee('price_test_granular');
    }

    public function test_settings_pages_reject_unauthorized_users_and_guests(): void
    {
        $outsider = User::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($outsider)
            ->get(route('admin.settings.index', ['tab' => 'plan']))
            ->assertForbidden();

        \Illuminate\Support\Facades\Auth::logout();
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------- checkout

    public function test_checkout_uses_server_side_price_and_never_exposes_secrets(): void
    {
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ], 200),
        ]);

        $response = $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->post(route('admin.settings.billing.checkout'), ['layer_key' => 'granular_rbac']);

        $response->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

        Http::assertSent(function ($request) {
            $body = $request->body();

            return str_contains($request->url(), '/v1/checkout/sessions')
                && str_contains($body, 'price_test_granular')
                && str_contains($body, 'company_id')
                && str_contains($body, 'granular_rbac')
                && ! str_contains($body, 'sk_test_not_a_real_key')
                && $request->hasHeader('Idempotency-Key');
        });
    }

    public function test_checkout_requires_step_up_and_rejects_unconfigured_layers(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.billing.checkout'), ['layer_key' => 'granular_rbac'])
            ->assertRedirectContains('/step-up');

        config(['services.stripe.prices.granular_rbac' => null]);
        $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->post(route('admin.settings.billing.checkout'), ['layer_key' => 'granular_rbac'])
            ->assertNotFound();
    }

    // ---------------------------------------------------------------- webhook

    public function test_webhook_rejects_invalid_and_stale_signatures(): void
    {
        $event = $this->checkoutEvent();

        $this->signedPost($event, false)->assertStatus(400);
        $this->assertDatabaseCount('billing_events', 0);
        $this->assertDatabaseCount('layer_entitlements', 0);

        $raw = json_encode($event, JSON_UNESCAPED_SLASHES);
        $old = time() - 900;
        $sig = hash_hmac('sha256', $old.'.'.$raw, 'whsec_test_secret');
        $this->call('POST', route('billing.stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$old.',v1='.$sig,
            'CONTENT_TYPE' => 'application/json',
        ], $raw)->assertStatus(400);
    }

    public function test_webhook_activates_and_is_idempotent(): void
    {
        $event = $this->checkoutEvent();

        $this->signedPost($event)->assertOk();
        $this->signedPost($event)->assertOk();

        $this->assertDatabaseCount('billing_events', 1);
        $this->assertDatabaseCount('layer_entitlements', 1);

        $entitlement = LayerEntitlement::query()->where('company_id', $this->company->id)->firstOrFail();
        $this->assertSame('active', $entitlement->status);
        $this->assertNotNull($entitlement->expires_at);
        $this->assertTrue($entitlement->expires_at->gt(now()->addDays(300)));
        $this->assertSame('cus_test_123', $entitlement->stripe_customer_id);
    }

    public function test_subscription_cancellation_revokes_only_the_paid_layer(): void
    {
        $metadata = [
            'company_id' => (string) $this->company->id,
            'layer_key' => 'granular_rbac',
        ];

        $this->signedPost([
            'id' => 'evt_sub_active',
            'type' => 'customer.subscription.updated',
            'data' => ['object' => [
                'id' => 'sub_test_123',
                'status' => 'active',
                'customer' => 'cus_test_123',
                'current_period_end' => now()->addMonth()->getTimestamp(),
                'metadata' => $metadata,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'active',
            'stripe_subscription_id' => 'sub_test_123',
        ]);

        $this->signedPost([
            'id' => 'evt_sub_deleted',
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => [
                'id' => 'sub_test_123',
                'customer' => 'cus_test_123',
                'metadata' => $metadata,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'revoked',
        ]);
    }

    // ------------------------------------------------------------ billing portal

    public function test_billing_portal_uses_the_stored_customer_id(): void
    {
        LayerEntitlement::create([
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'active',
            'stripe_customer_id' => 'cus_stored_123',
        ]);

        Http::fake([
            'api.stripe.com/v1/billing_portal/sessions' => Http::response([
                'url' => 'https://billing.stripe.com/p/session/test',
            ], 200),
        ]);

        $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->post(route('admin.settings.billing.portal'))
            ->assertRedirect('https://billing.stripe.com/p/session/test');

        Http::assertSent(function ($request) {
            $body = urldecode($request->body());

            return str_contains($body, 'cus_stored_123')
                && str_contains($body, '/admin/settings')
                && str_contains($body, 'tab=plan');
        });
    }

    public function test_plan_tab_shows_billing_portal_action_when_a_customer_exists(): void
    {
        LayerEntitlement::create([
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'active',
            'stripe_customer_id' => 'cus_stored_123',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.index', ['tab' => 'plan']))
            ->assertOk()
            ->assertSee('data-stripe-portal-status', false)
            ->assertSee('data-stripe-action="portal"', false)
            ->assertSee('Manage billing &amp; invoices in Stripe', false);
    }

    public function test_billing_portal_fails_closed_without_a_stripe_customer(): void
    {
        $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->post(route('admin.settings.billing.portal'))
            ->assertRedirect(route('admin.settings.index', ['tab' => 'plan']));
    }

    // ------------------------------------------------------------ security

    public function test_super_admin_cannot_access_another_tenants_record_by_id(): void
    {
        $otherCompany = Company::create(['name' => 'Other Co', 'email' => 'other@example.test', 'is_active' => true]);
        $otherCustomer = \App\Models\Customer::create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Customer',
            'email' => 'other-customer@example.test',
        ]);

        $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->get(route('customers.show', $otherCustomer))
            ->assertForbidden();
    }

    public function test_step_up_rejects_protocol_relative_open_redirect(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('step-up.verify'), [
                'password' => 'password',
                'intended' => 'https://evil.example//evil.example',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertStringNotContainsString('evil.example', (string) $response->headers->get('Location'));
    }

    protected function checkoutEvent(): array
    {
        return [
            'id' => 'evt_test_checkout_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_123',
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'customer' => 'cus_test_123',
                'client_reference_id' => (string) $this->company->id,
                'metadata' => [
                    'company_id' => (string) $this->company->id,
                    'layer_key' => 'ai_credits',
                    'lookup_key' => 'ai_credits',
                    'validity_days' => '365',
                ],
            ]],
        ];
    }
}
