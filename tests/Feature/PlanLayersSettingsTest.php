<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PlanLayersSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_tab_loads_for_authorized_admin(): void
    {
        // Keep this UI test deterministic regardless of the developer's .env.
        config(['services.stripe.secret' => null, 'services.stripe.webhook_secret' => null]);

        $company = Company::create(['name' => 'Test Co', 'email' => 'plan@t.com', 'is_active' => true]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findOrCreate('super_admin', 'web');
        Permission::findOrCreate('settings.configure', 'web');
        if (! $role->hasPermissionTo('settings.configure')) {
            $role->givePermissionTo('settings.configure');
        }
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)
            ->get(route('admin.settings.index', ['tab' => 'plan']))
            ->assertStatus(200)
            ->assertSee('Plan & Layers')
            ->assertSee('AI usage (company, recorded)')
            ->assertSee('Stripe billing status')
            ->assertSee('STRIPE_SECRET')
            ->assertSee('STRIPE_WEBHOOK_SECRET')
            ->assertSee('STRIPE_PRICE_GRANULAR_RBAC')
            ->assertSee('AppSumo codes')
            ->assertSee('$49', false)
            ->assertSee('$98', false)
            ->assertSee('$499', false)
            ->assertSee('data-stripe-billing-status', false)
            ->assertSee('data-stripe-portal-status', false)
            ->assertSee('data-stripe-layer-card="granular_rbac"', false)
            ->assertDontSee('data-stripe-checkout-ready', false);

        $html = $response->getContent();
        $this->assertSame(
            count(config('layers.layers', [])),
            substr_count($html, 'data-stripe-layer-card="'),
            'Every configured add-on layer must render as a visible card.'
        );
        $this->assertStringContainsString('Not set up yet? No problem.', $html);
        $this->assertStringContainsString('LICENSE TOKEN (ADVANCED)', $html);
    }

    public function test_configured_price_enables_checkout_without_rendering_secret_values(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_plan_ui_must_not_render',
            'services.stripe.prices.granular_rbac' => 'price_test_plan_ui_must_not_render',
        ]);

        $company = Company::create(['name' => 'Billing UI Co', 'email' => 'plan-ui@t.com', 'is_active' => true]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findOrCreate('super_admin', 'web');
        Permission::findOrCreate('settings.configure', 'web');
        if (! $role->hasPermissionTo('settings.configure')) {
            $role->givePermissionTo('settings.configure');
        }
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get(route('admin.settings.index', ['tab' => 'plan']))
            ->assertOk()
            ->assertSee('Checkout ready')
            ->assertSee('data-stripe-checkout-ready', false)
            ->assertDontSee('sk_test_plan_ui_must_not_render')
            ->assertDontSee('price_test_plan_ui_must_not_render');
    }
}
