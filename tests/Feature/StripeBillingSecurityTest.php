<?php

namespace Tests\Feature;

use App\Models\BillingEvent;
use App\Models\Company;
use App\Models\Customer;
use App\Models\LayerEntitlement;
use App\Models\User;
use App\Services\Billing\EntitlementActivationService;
use App\Services\Billing\StripeCheckoutService;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use RuntimeException;
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
            'password' => Hash::make('password'),
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

    protected function signedPost(array $event, bool $valid = true): TestResponse
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

        Auth::logout();
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
        $otherCustomer = Customer::create([
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

    public function test_unpaid_checkout_does_not_activate_entitlement(): void
    {
        $event = $this->checkoutEvent();
        $event['data']['object']['payment_status'] = 'unpaid';

        $this->signedPost($event)->assertOk();

        $this->assertDatabaseCount('layer_entitlements', 0);
    }

    public function test_stale_event_cannot_reactivate_revoked_entitlement(): void
    {
        $metadata = [
            'company_id' => (string) $this->company->id,
            'layer_key' => 'granular_rbac',
        ];
        $base = CarbonImmutable::now();

        $this->signedPost([
            'id' => 'evt_revoke_latest',
            'type' => 'customer.subscription.deleted',
            'created' => $base->subMinutes(5)->getTimestamp(),
            'data' => ['object' => [
                'id' => 'sub_order_1',
                'customer' => 'cus_order_1',
                'metadata' => $metadata,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'revoked',
        ]);

        $this->signedPost([
            'id' => 'evt_active_stale',
            'type' => 'customer.subscription.updated',
            'created' => $base->subMinutes(30)->getTimestamp(),
            'data' => ['object' => [
                'id' => 'sub_order_1',
                'status' => 'active',
                'customer' => 'cus_order_1',
                'current_period_end' => $base->addMonth()->getTimestamp(),
                'metadata' => $metadata,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'revoked',
        ]);
    }

    public function test_stuck_processing_claim_is_retried(): void
    {
        $event = $this->checkoutEvent();
        $event['id'] = 'evt_stuck_claim';
        $event['created'] = CarbonImmutable::now()->getTimestamp();

        BillingEvent::create([
            'event_id' => $event['id'],
            'event_type' => $event['type'],
            'event_created_at' => $event['created'],
            'status' => 'processing',
            'claimed_at' => CarbonImmutable::now()->subHour(),
            'payload' => [],
        ]);

        $this->signedPost($event)->assertOk();

        $this->assertDatabaseHas('billing_events', [
            'event_id' => $event['id'],
            'status' => 'processed',
        ]);
        $this->assertDatabaseCount('layer_entitlements', 1);
    }

    public function test_conflicting_metadata_cannot_move_subscription_to_another_tenant(): void
    {
        $other = Company::create(['name' => 'Other Co', 'email' => 'other-tenant@example.test', 'is_active' => true]);

        LayerEntitlement::create([
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'active',
            'stripe_subscription_id' => 'sub_conflict',
        ]);

        $this->signedPost([
            'id' => 'evt_conflict',
            'type' => 'customer.subscription.updated',
            'created' => CarbonImmutable::now()->getTimestamp(),
            'data' => ['object' => [
                'id' => 'sub_conflict',
                'status' => 'active',
                'current_period_end' => CarbonImmutable::now()->addMonth()->getTimestamp(),
                'metadata' => [
                    'company_id' => (string) $other->id,
                    'layer_key' => 'granular_rbac',
                ],
            ]],
        ])->assertOk();

        $this->assertDatabaseMissing('layer_entitlements', ['company_id' => $other->id]);
        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'active',
        ]);
    }

    public function test_empty_customer_id_does_not_wipe_stored_customer(): void
    {
        $service = app(EntitlementActivationService::class);
        $expiresAt = CarbonImmutable::now()->addDay();

        $entitlement = $service->activate($this->company->id, 'granular_rbac', 'active', $expiresAt, [
            'subscription_id' => 'sub_keep_customer',
            'customer_id' => 'cus_keep_123',
        ]);
        $service->activate($this->company->id, 'granular_rbac', 'active', $expiresAt, [
            'subscription_id' => 'sub_keep_customer',
            'customer_id' => '',
        ]);

        $entitlement->refresh();
        $this->assertSame('cus_keep_123', $entitlement->stripe_customer_id);
    }

    public function test_checkout_rejects_unsafe_api_base_without_sending_request(): void
    {
        config(['services.stripe.api_base' => 'https://evil.example']);
        Http::fake();

        try {
            app(StripeCheckoutService::class)->createSession($this->company->id, 'granular_rbac');
            $this->fail('Unsafe Stripe API base was accepted.');
        } catch (RuntimeException) {
            Http::assertNothingSent();
        }
    }

    public function test_checkout_rejects_session_url_from_unexpected_host(): void
    {
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_evil',
                'url' => 'https://evil.example/pay/cs_evil',
            ], 200),
        ]);

        $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->post(route('admin.settings.billing.checkout'), ['layer_key' => 'granular_rbac'])
            ->assertRedirect(route('admin.settings.index', ['tab' => 'plan']))
            ->assertSessionHas('error', 'Stripe checkout is temporarily unavailable.');

        $this->assertDatabaseCount('layer_entitlements', 0);
    }

    public function test_portal_rejects_return_url_on_another_host(): void
    {
        LayerEntitlement::create([
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'active',
            'stripe_customer_id' => 'cus_stored_123',
        ]);
        config(['services.stripe.portal_return_url' => 'https://evil.example/return']);
        Http::fake();

        $this->withSession($this->adminSession())
            ->actingAs($this->admin)
            ->post(route('admin.settings.billing.portal'))
            ->assertRedirect(route('admin.settings.index', ['tab' => 'plan']))
            ->assertSessionHas('error', 'Stripe billing management is temporarily unavailable.');

        Http::assertNothingSent();
    }

    public function test_async_payment_succeeded_activates_entitlement(): void
    {
        $event = $this->checkoutEvent();
        $event['id'] = 'evt_async_success';
        $event['type'] = 'checkout.session.async_payment_succeeded';

        $this->signedPost($event)->assertOk();

        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'ai_credits',
            'status' => 'active',
        ]);
    }

    public function test_invoice_paid_activates_entitlement_with_period_end(): void
    {
        $periodEnd = CarbonImmutable::now()->addMonth()->getTimestamp();

        $this->signedPost([
            'id' => 'evt_invoice_paid',
            'type' => 'invoice.paid',
            'created' => CarbonImmutable::now()->getTimestamp(),
            'data' => ['object' => [
                'id' => 'in_test_123',
                'subscription' => 'sub_invoice_123',
                'customer' => 'cus_invoice_123',
                'lines' => ['data' => [[
                    'subscription' => 'sub_invoice_123',
                    'current_period_end' => $periodEnd,
                ]]],
                'metadata' => [
                    'company_id' => (string) $this->company->id,
                    'layer_key' => 'granular_rbac',
                ],
            ]],
        ])->assertOk();

        $entitlement = LayerEntitlement::query()
            ->where('company_id', $this->company->id)
            ->where('layer_key', 'granular_rbac')
            ->firstOrFail();

        $this->assertSame('active', $entitlement->status);
        $this->assertSame('sub_invoice_123', $entitlement->stripe_subscription_id);
        $this->assertNotNull($entitlement->expires_at);
    }

    public function test_invoice_payment_failed_sets_bounded_grace(): void
    {
        $this->signedPost([
            'id' => 'evt_invoice_failed',
            'type' => 'invoice.payment_failed',
            'created' => CarbonImmutable::now()->getTimestamp(),
            'data' => ['object' => [
                'id' => 'in_failed_123',
                'subscription' => 'sub_failed_123',
                'customer' => 'cus_invoice_123',
                'metadata' => [
                    'company_id' => (string) $this->company->id,
                    'layer_key' => 'granular_rbac',
                ],
            ]],
        ])->assertOk();

        $entitlement = LayerEntitlement::query()
            ->where('company_id', $this->company->id)
            ->where('layer_key', 'granular_rbac')
            ->firstOrFail();

        $this->assertSame('grace', $entitlement->status);
        $this->assertNotNull($entitlement->expires_at);
        $this->assertTrue($entitlement->expires_at->gt(now()));
    }

    public function test_cancellation_never_deletes_business_records(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Keep This Customer',
            'email' => 'keep-customer@example.test',
        ]);

        $this->signedPost([
            'id' => 'evt_cancel_keep_data',
            'type' => 'customer.subscription.deleted',
            'created' => CarbonImmutable::now()->getTimestamp(),
            'data' => ['object' => [
                'id' => 'sub_keep_data',
                'customer' => 'cus_keep_data',
                'metadata' => [
                    'company_id' => (string) $this->company->id,
                    'layer_key' => 'granular_rbac',
                ],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('layer_entitlements', [
            'company_id' => $this->company->id,
            'layer_key' => 'granular_rbac',
            'status' => 'revoked',
        ]);
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
