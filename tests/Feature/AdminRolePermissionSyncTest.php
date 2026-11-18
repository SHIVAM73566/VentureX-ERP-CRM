<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminRolePermissionSyncTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['inventory.view', 'sales.view', 'finance.view'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $company = Company::create([
            'name' => 'RBAC Test Company',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'RBAC Admin',
            'email' => 'rbac-admin@test.com',
            'password' => Hash::make('Password123!'),
            'company_id' => $company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdmin);

        $this->actingAs($this->admin);
    }

    public function test_store_resolves_permission_ids_before_syncing(): void
    {
        $ids = Permission::whereIn('name', ['inventory.view', 'sales.view'])->pluck('id')->all();

        $response = $this->post(route('admin.roles.store'), [
            'name' => 'ops_sync_test',
            'label' => 'Ops Sync Test',
            'permissions' => $ids,
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success', 'Role created.');

        $role = Role::findByName('ops_sync_test');
        $this->assertEqualsCanonicalizing(
            ['inventory.view', 'sales.view'],
            $role->permissions->pluck('name')->all()
        );
    }

    public function test_update_resolves_permission_ids_before_syncing(): void
    {
        $role = Role::create(['name' => 'ops_update_test', 'guard_name' => 'web']);
        $role->syncPermissions([Permission::findByName('inventory.view')]);

        $financeId = Permission::findByName('finance.view')->id;

        $response = $this->put(route('admin.roles.update', $role), [
            'name' => 'ops_update_test',
            'permissions' => [$financeId],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success', 'Role updated.');

        $this->assertEqualsCanonicalizing(
            ['finance.view'],
            $role->fresh()->permissions->pluck('name')->all()
        );
    }
}
