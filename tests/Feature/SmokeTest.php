<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CompanyContext;
use Database\Seeders\CompanySeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\EnsureTwoFactor::class);

        if (! User::where('email', 'admin@example.com')->exists() && ! User::where('email', 'admin@jainmetal.example')->exists()) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->seed([
                PermissionSeeder::class,
                RoleSeeder::class,
                MasterDataSeeder::class,
                CompanySeeder::class,
            ]);
        }

        $admin = User::where('email', 'admin@example.com')->first() ?? User::where('email', 'admin@jainmetal.example')->first();
        if ($admin) {
            $admin->update(['password' => Hash::make('password')]);
        }
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@example.com')->first() ?? User::where('email', 'admin@jainmetal.example')->firstOrFail();
    }

    public function test_crm_customers_index_loads(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        if ($admin->company_id) {
            CompanyContext::set($admin->company_id);
        }
        $response = $this->get('/customers');
        $response->assertStatus(200);
    }

    public function test_inventory_products_index_loads(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        if ($admin->company_id) {
            CompanyContext::set($admin->company_id);
        }
        $response = $this->get('/inventory/products');
        $response->assertStatus(200);
    }

    public function test_finance_invoices_index_loads(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        if ($admin->company_id) {
            CompanyContext::set($admin->company_id);
        }
        $response = $this->get('/sales/invoices');
        $response->assertStatus(200);
    }

    public function test_logistics_shipments_index_loads(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        if ($admin->company_id) {
            CompanyContext::set($admin->company_id);
        }
        $response = $this->get('/logistics/shipments');
        $response->assertStatus(200);
    }

    public function test_procurement_purchase_orders_index_loads(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        if ($admin->company_id) {
            CompanyContext::set($admin->company_id);
        }
        $response = $this->get('/procurement/orders');
        $response->assertStatus(200);
    }
}
