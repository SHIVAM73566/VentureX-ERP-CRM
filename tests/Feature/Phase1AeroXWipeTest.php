<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use App\Services\Ai\AgenticEngine;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class Phase1AeroXWipeTest extends TestCase
{
    private Company $company;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->company = Company::create([
            'name' => 'Aero X Test Co',
            'currency_code' => 'USD',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'password' => 'CurrentPassword123!',
        ]);
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
    }

    public function test_wipe_demo_data_is_not_a_default_agentic_engine_action(): void
    {
        $keys = collect(AgenticEngine::defaults())->pluck('key')->all();

        $this->assertNotContains('wipe_demo_data', $keys);
    }

    public function test_aero_x_rejects_wrong_current_password(): void
    {
        $customer = $this->demoCustomer();

        $this->aeroUser()->postJson(route('admin.demo-data.aero-x.wipe'), $this->payload([
            'password' => 'WrongPassword123!',
        ]))->assertStatus(423);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertSame(0, AuditLog::where('event', 'demo_data_wipe')->count());
    }

    public function test_aero_x_rejects_wrong_confirmation_phrase(): void
    {
        $customer = $this->demoCustomer();

        $this->aeroUser()->postJson(route('admin.demo-data.aero-x.wipe'), $this->payload([
            'confirmation' => 'DELETE DEMO DATA',
        ]))->assertStatus(422);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_aero_x_returns_confirmation_payload_without_writing(): void
    {
        $customer = $this->demoCustomer();

        $response = $this->aeroUser()->postJson(route('admin.demo-data.aero-x.wipe'), $this->payload([
            'confirm' => false,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('action_key', 'wipe_demo_data')
            ->assertJsonPath('requires_confirmation', true)
            ->assertJsonPath('preview.customers', 1);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertSame(0, AuditLog::where('event', 'demo_data_wipe')->count());
    }

    public function test_aero_x_executes_only_after_confirmation_payload(): void
    {
        $customer = $this->demoCustomer();
        $user = $this->aeroUser();

        $user->postJson(route('admin.demo-data.aero-x.wipe'), $this->payload(['confirm' => false]))
            ->assertOk()
            ->assertJsonPath('requires_confirmation', true);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);

        $user->postJson(route('admin.demo-data.aero-x.wipe'), $this->payload(['confirm' => true]))
            ->assertOk()
            ->assertJsonPath('requires_confirmation', false)
            ->assertJsonPath('action_key', 'wipe_demo_data');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertSame(1, AuditLog::where('event', 'demo_data_wipe')->count());
        $this->assertSame(1, AuditLog::where('event', 'agent.manual_wipe')->count());
    }

    private function aeroUser(): self
    {
        return $this->actingAs($this->admin)->withSession([
            'step_up_verified_at' => now()->timestamp,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'action_key' => 'wipe_demo_data',
            'confirm' => false,
            'password' => 'CurrentPassword123!',
            'confirmation' => 'WIPE DEMO DATA',
        ], $overrides);
    }

    private function demoCustomer(): Customer
    {
        return Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Aero Demo Customer',
            'email' => 'aero-demo@example.test',
            'status' => 'active',
        ]);
    }
}
