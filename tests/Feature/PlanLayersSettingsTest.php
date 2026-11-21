<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PlanLayersSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_tab_loads_for_authorized_admin(): void
    {
        $company = Company::create(['name' => 'Test Co', 'email' => 'plan@t.com', 'is_active' => true]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $role = \Spatie\Permission\Models\Role::findOrCreate('super_admin', 'web');
        Permission::findOrCreate('settings.configure', 'web');
        if (! $role->hasPermissionTo('settings.configure')) {
            $role->givePermissionTo('settings.configure');
        }
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get(route('admin.settings.index', ['tab' => 'plan']))
            ->assertStatus(200)
            ->assertSee('Plan & Layers');
    }
}
