<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\Demo\DemoDataWipeService;
use App\Services\SettingService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class Phase1DemoWipeTest extends TestCase
{
    private Company $company;

    private User $admin;

    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsureTwoFactor::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->company = Company::create([
            'name' => 'Wipe Test Co',
            'currency_code' => 'USD',
            'is_active' => true,
        ]);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'password' => 'CurrentPassword123!',
        ]);
        $this->admin->assignRole($superAdmin);

        $this->viewer = User::factory()->create(['company_id' => $this->company->id]);
        $this->viewer->assignRole($viewerRole);
    }

    public function test_demo_data_page_requires_auth_super_admin_and_step_up(): void
    {
        $this->get(route('admin.demo-data.index'))->assertRedirect();

        $this->actingAs($this->viewer)
            ->withSession($this->stepUp())
            ->get(route('admin.demo-data.index'))
            ->assertForbidden();

        $this->flushSession();

        // Step-up accepts only a local path+query as `intended` so an
        // attacker cannot turn it into an open redirect.
        $this->actingAs($this->admin)
            ->get(route('admin.demo-data.index'))
            ->assertRedirect(route('step-up.show', ['intended' => '/admin/demo-data']));

        $this->adminUser()
            ->get(route('admin.demo-data.index'))
            ->assertOk()
            ->assertSee('WIPE DEMO DATA');
    }

    public function test_manual_wipe_requires_current_password(): void
    {
        $customer = $this->demoCustomer();

        $this->adminUser()
            ->from(route('admin.demo-data.index'))
            ->post(route('admin.demo-data.wipe'), [
                'password' => 'WrongPassword123!',
                'confirmation' => 'WIPE DEMO DATA',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertSame(0, AuditLog::where('event', 'demo_data_wipe')->count());
    }

    public function test_manual_wipe_requires_exact_phrase(): void
    {
        $customer = $this->demoCustomer();

        $this->adminUser()
            ->from(route('admin.demo-data.index'))
            ->post(route('admin.demo-data.wipe'), [
                'password' => 'CurrentPassword123!',
                'confirmation' => 'wipe demo data',
            ])
            ->assertSessionHasErrors('confirmation');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_manual_wipe_removes_demo_rows_and_preserves_structure(): void
    {
        $customer = $this->demoCustomer();
        $setting = app(SettingService::class)->set('currency.default', 'USD', ['company_id' => $this->company->id]);
        $account = $this->account();

        $this->adminUser()
            ->from(route('admin.demo-data.index'))
            ->post(route('admin.demo-data.wipe'), [
                'password' => 'CurrentPassword123!',
                'confirmation' => 'WIPE DEMO DATA',
            ])
            ->assertRedirect(route('admin.demo-data.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('settings', ['id' => $setting->id, 'value' => 'USD']);
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => User::class,
            'model_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'demo_data_wipe',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_wipe_removes_demo_accounts_but_preserves_the_buyer_admin(): void
    {
        $customer = $this->demoCustomer();
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $demoIds = [];

        foreach (array_merge(DemoDataWipeService::DEMO_EMAILS, [DemoDataWipeService::SEEDED_DEMO_EMAILS[0]]) as $email) {
            $demo = User::factory()->create([
                'company_id' => $this->company->id,
                'email' => $email,
            ]);
            $demo->assignRole($viewerRole);
            $demoIds[] = $demo->id;
        }

        $this->adminUser()
            ->from(route('admin.demo-data.index'))
            ->post(route('admin.demo-data.wipe'), [
                'password' => 'CurrentPassword123!',
                'confirmation' => 'WIPE DEMO DATA',
            ])
            ->assertRedirect(route('admin.demo-data.index'));

        foreach ($demoIds as $id) {
            $this->assertDatabaseMissing('users', ['id' => $id]);
        }
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => User::class,
            'model_id' => $this->admin->id,
        ]);
    }

    public function test_wipe_service_is_idempotent_and_never_touches_protected_tables(): void
    {
        CompanyContext::set($this->company->id);
        $this->actingAs($this->admin);

        $customer = $this->demoCustomer();
        $setting = app(SettingService::class)->set('currency.default', 'USD', ['company_id' => $this->company->id]);
        $account = $this->account();

        $service = app(DemoDataWipeService::class);

        $first = $service->wipe($this->company->id);
        $second = $service->wipe($this->company->id);

        $this->assertGreaterThan(0, $first['customers']);
        $this->assertSame(0, $second['customers']);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('settings', ['id' => $setting->id]);
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        foreach (DemoDataWipeService::PROTECTED_TABLES as $table) {
            $this->assertNotContains($table, array_keys($first));
        }

        $this->assertGreaterThanOrEqual(2, AuditLog::where('event', 'demo_data_wipe')->count());
    }

    private function adminUser(): self
    {
        return $this->actingAs($this->admin)->withSession($this->stepUp());
    }

    /**
     * @return array<string, int>
     */
    private function stepUp(): array
    {
        return ['step_up_verified_at' => now()->timestamp];
    }

    private function demoCustomer(): Customer
    {
        return Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Demo Customer',
            'email' => 'demo-customer@example.test',
            'status' => 'active',
        ]);
    }

    private function account(): Account
    {
        return Account::create([
            'company_id' => $this->company->id,
            'code' => 'CASH',
            'name' => 'Cash',
            'type' => 'asset',
            'is_active' => true,
        ]);
    }
}
