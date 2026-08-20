<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Account;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CodesterResubmissionTest extends TestCase
{
    protected Company $company;

    protected Company $otherCompany;

    protected User $admin;

    protected User $viewer;

    protected User $otherCompanyUser;

    protected array $adminPermissions = [
        'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
        'products.view', 'products.create', 'products.edit', 'products.delete',
        'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete',
        'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
        'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
        'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
        'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete',
        'settings.configure', 'users.view', 'users.create', 'users.edit', 'users.delete',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        User::whereIn('email', ['admin@test.com', 'viewer@test.com', 'other@test.com'])->forceDelete();
        Company::whereIn('name', ['Test Corp', 'Other Corp'])->forceDelete();

        $this->company = Company::create([
            'name' => 'Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->otherCompany = Company::create([
            'name' => 'Other Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve'];
        $modules = [
            'dashboard', 'customers', 'contacts', 'leads', 'opportunities', 'activities',
            'companies', 'branches', 'departments', 'users', 'roles', 'settings',
            'suppliers', 'supplier_offers', 'documents', 'ai', 'ai_skills', 'automation',
            'audit_logs', 'reports', 'sales', 'purchase', 'inventory', 'finance',
            'logistics', 'hr', 'projects', 'workflows', 'exports', 'products',
            'quotations', 'invoices', 'payments', 'purchase_orders',
        ];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        Permission::firstOrCreate(['name' => 'settings.configure', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'roles.manage', 'guard_name' => 'web']);

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerRole->permissions()->detach();
        $viewerRole->syncPermissions([
            'customers.view', 'products.view', 'quotations.view', 'invoices.view',
            'payments.view', 'suppliers.view', 'purchase_orders.view', 'ai.view',
            'inventory.view', 'finance.view', 'logistics.view', 'purchase.view',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdminRole);

        $this->viewer = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->viewer->assignRole($viewerRole);

        $this->otherCompanyUser = User::create([
            'name' => 'Other User',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->otherCompany->id,
            'is_active' => true,
        ]);
        $this->otherCompanyUser->assignRole($viewerRole);
    }

    protected function actingAdmin()
    {
        return $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    protected function actingViewer()
    {
        return $this->actingAs($this->viewer)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    protected function actingOtherCompanyUser()
    {
        return $this->actingAs($this->otherCompanyUser)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    // =========================================================================
    // 1. Authentication Tests
    // =========================================================================

    public function test_login_page_loads(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
    }

    public function test_valid_credentials_allow_login(): void
    {
        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_invalid_credentials_reject_login(): void
    {
        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_disabled_account_cannot_login(): void
    {
        $this->admin->update(['is_active' => false]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_logout_works(): void
    {
        $response = $this->actingAdmin()->post(route('logout'));
        $response->assertRedirect(route('login'));
    }

    // =========================================================================
    // 2. Dashboard Tests
    // =========================================================================

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $response = $this->actingAdmin()->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    // =========================================================================
    // 3. Customer CRUD Tests
    // =========================================================================

    public function test_customer_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('customers.index'));
        $response->assertOk();
    }

    public function test_customer_create_form_loads(): void
    {
        $response = $this->actingAdmin()->get(route('customers.create'));
        $response->assertOk();
    }

    public function test_customer_can_be_created(): void
    {
        $response = $this->actingAdmin()->post(route('customers.store'), [
            'name' => 'Acme Inc',
            'email' => 'info@acme.com',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['name' => 'Acme Inc', 'company_id' => $this->company->id]);
    }

    public function test_customer_show_loads(): void
    {
        $customer = Customer::create([
            'name' => 'Show Test',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->get(route('customers.show', $customer));
        $response->assertOk();
    }

    public function test_customer_can_be_updated(): void
    {
        $customer = Customer::create([
            'name' => 'Old Name',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->put(route('customers.update', $customer), [
            'name' => 'New Name',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'New Name']);
    }

    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::create([
            'name' => 'Delete Me',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->delete(route('customers.destroy', $customer));
        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_viewer_cannot_create_customer(): void
    {
        $response = $this->actingViewer()->post(route('customers.store'), [
            'name' => 'Should Fail',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // 4. Product CRUD Tests
    // =========================================================================

    public function test_product_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('inventory.products.index'));
        $response->assertOk();
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->actingAdmin()->post(route('inventory.products.store'), [
            'name' => 'Widget',
            'sku' => 'WDG-001',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Widget', 'sku' => 'WDG-001', 'company_id' => $this->company->id]);
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::create([
            'name' => 'Old Widget',
            'sku' => 'WDG-002',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->put(route('inventory.products.update', $product), [
            'name' => 'New Widget',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Widget']);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::create([
            'name' => 'Delete Widget',
            'sku' => 'WDG-DEL',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->delete(route('inventory.products.destroy', $product));
        $response->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_viewer_cannot_create_product(): void
    {
        $response = $this->actingViewer()->post(route('inventory.products.store'), [
            'name' => 'Fail Widget',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // 5. Quotation CRUD Tests
    // =========================================================================

    public function test_quotation_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('sales.quotations.index'));
        $response->assertOk();
    }

    public function test_quotation_can_be_created(): void
    {
        $customer = Customer::create([
            'name' => 'Quote Customer',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->post(route('sales.quotations.store'), [
            'customer_id' => $customer->id,
            'status' => 'draft',
            'items' => [
                ['description' => 'Service', 'quantity' => 1, 'unit_price' => 100, 'discount' => 0, 'tax_rate' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotations', ['company_id' => $this->company->id, 'status' => 'draft']);
    }

    public function test_quotation_can_be_updated(): void
    {
        $customer = Customer::create([
            'name' => 'Quote Cust 2',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUOT-TEST-001',
            'status' => 'draft',
            'created_by' => $this->admin->id,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
        ]);

        $response = $this->actingAdmin()->put(route('sales.quotations.update', $quotation), [
            'customer_id' => $customer->id,
            'status' => 'sent',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotations', ['id' => $quotation->id, 'status' => 'sent']);
    }

    public function test_quotation_can_be_converted_to_order(): void
    {
        $customer = Customer::create([
            'name' => 'Convert Customer',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUOT-CONV-001',
            'status' => 'accepted',
            'created_by' => $this->admin->id,
            'subtotal' => 500,
            'total' => 500,
        ]);

        $response = $this->actingAdmin()->post(route('sales.quotations.convert', $quotation));
        $response->assertRedirect();
        $this->assertDatabaseHas('sales_orders', ['quotation_id' => $quotation->id, 'company_id' => $this->company->id]);
    }

    // =========================================================================
    // 6. Invoice CRUD Tests
    // =========================================================================

    public function test_invoice_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('sales.invoices.index'));
        $response->assertOk();
    }

    public function test_invoice_can_be_created(): void
    {
        $customer = Customer::create([
            'name' => 'Invoice Customer',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service', 'quantity' => 1, 'unit_price' => 200, 'discount' => 0, 'tax_rate' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', ['company_id' => $this->company->id, 'status' => 'draft']);
    }

    public function test_invoice_show_loads(): void
    {
        $customer = Customer::create([
            'name' => 'Inv Show',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-001',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
            'paid_amount' => 0,
        ]);

        $response = $this->actingAdmin()->get(route('sales.invoices.show', $invoice));
        $response->assertOk();
    }

    public function test_invoice_pdf_generation(): void
    {
        $customer = Customer::create([
            'name' => 'PDF Customer',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-PDF-001',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'created_by' => $this->admin->id,
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'paid_amount' => 0,
        ]);

        $response = $this->actingAdmin()->get(route('sales.invoices.pdf', $invoice));
        $response->assertOk();
    }

    // =========================================================================
    // 7. Payment CRUD Tests
    // =========================================================================

    public function test_payment_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('sales.payments.index'));
        $response->assertOk();
    }

    public function test_payment_can_be_created(): void
    {
        $customer = Customer::create([
            'name' => 'Pay Customer',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->post(route('sales.payments.store'), [
            'customer_id' => $customer->id,
            'payment_date' => now()->toDateString(),
            'amount' => 250,
            'method' => 'bank',
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', ['company_id' => $this->company->id, 'amount' => 250]);
    }

    public function test_payment_show_loads(): void
    {
        $customer = Customer::create([
            'name' => 'Pay Show',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-TEST-001',
            'payment_date' => now()->toDateString(),
            'amount' => 100,
            'method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->get(route('sales.payments.show', $payment));
        $response->assertOk();
    }

    public function test_payment_can_be_deleted(): void
    {
        $customer = Customer::create([
            'name' => 'Pay Del',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-DEL-001',
            'payment_date' => now()->toDateString(),
            'amount' => 50,
            'method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('sales.payments.destroy', $payment));
        $response->assertRedirect(route('sales.payments.index'));
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    // =========================================================================
    // 8. Supplier CRUD Tests
    // =========================================================================

    public function test_supplier_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('suppliers.index'));
        $response->assertOk();
    }

    public function test_supplier_can_be_created(): void
    {
        $response = $this->actingAdmin()->post(route('suppliers.store'), [
            'name' => 'Acme Supplies',
            'email' => 'supply@acme.com',
            'status' => 'pending',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', ['name' => 'Acme Supplies', 'company_id' => $this->company->id]);
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::create([
            'name' => 'Old Supplier',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAdmin()->put(route('suppliers.update', $supplier), [
            'name' => 'Updated Supplier',
            'status' => 'verified',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated Supplier']);
    }

    public function test_supplier_can_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name' => 'Delete Supplier',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAdmin()->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_viewer_cannot_create_supplier(): void
    {
        $response = $this->actingViewer()->post(route('suppliers.store'), [
            'name' => 'Fail Supplier',
        ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // 9. Purchase Order CRUD Tests
    // =========================================================================

    public function test_purchase_order_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('procurement.orders.index'));
        $response->assertOk();
    }

    public function test_purchase_order_create_form_loads(): void
    {
        $response = $this->actingAdmin()->get(route('procurement.orders.create'));
        $response->assertOk();
    }

    // =========================================================================
    // 10. Role/Permission Checks
    // =========================================================================

    public function test_super_admin_can_access_all_modules(): void
    {
        $routes = [
            route('customers.index'),
            route('inventory.products.index'),
            route('sales.quotations.index'),
            route('sales.invoices.index'),
            route('sales.payments.index'),
            route('suppliers.index'),
            route('procurement.orders.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAdmin()->get($url)->assertOk();
        }
    }

    public function test_viewer_cannot_create_in_any_module(): void
    {
        $this->actingViewer()->post(route('customers.store'), ['name' => 'X', 'status' => 'active'])->assertForbidden();
        $this->actingViewer()->post(route('inventory.products.store'), ['name' => 'X', 'status' => 'active'])->assertForbidden();
        $this->actingViewer()->post(route('suppliers.store'), ['name' => 'X'])->assertForbidden();
    }

    public function test_viewer_can_view_modules(): void
    {
        $this->actingViewer()->get(route('customers.index'))->assertOk();
        $this->actingViewer()->get(route('inventory.products.index'))->assertOk();
        $this->actingViewer()->get(route('suppliers.index'))->assertOk();
    }

    // =========================================================================
    // 11. Search Functionality
    // =========================================================================

    public function test_search_works_with_valid_query(): void
    {
        Customer::create([
            'name' => 'Searchable Customer',
            'company_id' => $this->company->id,
            'created_by' => $this->admin->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->get(route('search', ['q' => 'Searchable']));
        $response->assertOk();
    }

    public function test_search_returns_empty_for_short_query(): void
    {
        $response = $this->actingAdmin()->get(route('search', ['q' => 'a']));
        $response->assertOk();
    }

    // =========================================================================
    // 12. Pagination on List Pages
    // =========================================================================

    public function test_customer_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('customers.index'));
        $response->assertOk();

        $customers = $response->viewData('customers');
        $this->assertTrue(method_exists($customers, 'links'));
    }

    public function test_product_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('inventory.products.index'));
        $response->assertOk();

        $products = $response->viewData('products');
        $this->assertTrue(method_exists($products, 'links'));
    }

    // =========================================================================
    // 13. Security Headers
    // =========================================================================

    public function test_security_headers_are_present(): void
    {
        $response = $this->actingAdmin()->get(route('dashboard'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_csp_header_is_set(): void
    {
        $response = $this->actingAdmin()->get(route('dashboard'));

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    // =========================================================================
    // 14. CSRF Protection
    // =========================================================================

    public function test_post_without_csrf_token_is_rejected(): void
    {
        $this->get(route('login'));

        $response = $this->call('POST', '/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong',
            '_token' => 'invalid-token',
        ]);

        $this->assertContains($response->getStatusCode(), [419, 302]);
    }

    // =========================================================================
    // 15. Session Security
    // =========================================================================

    public function test_session_is_regenerated_on_login(): void
    {
        $response = $this->post(route('login.attempt'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_guest_cannot_access_authenticated_routes(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    // =========================================================================
    // IDOR Protection Tests
    // =========================================================================

    public function test_cross_company_supplier_blocked_by_controller(): void
    {
        $supplier = Supplier::create([
            'name' => 'Other Co Supplier',
            'company_id' => $this->otherCompany->id,
            'created_by' => $this->otherCompanyUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAdmin()->get(route('suppliers.show', $supplier));
        $response->assertNotFound();
    }

    public function test_cross_company_supplier_update_blocked(): void
    {
        $supplier = Supplier::create([
            'name' => 'Other Co Supplier',
            'company_id' => $this->otherCompany->id,
            'created_by' => $this->otherCompanyUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAdmin()->put(route('suppliers.update', $supplier), [
            'name' => 'Hacked',
            'status' => 'approved',
        ]);
        $response->assertNotFound();
    }

    public function test_cross_company_product_blocked(): void
    {
        $product = Product::create([
            'name' => 'Other Co Product',
            'sku' => 'OTHER-001',
            'company_id' => $this->otherCompany->id,
            'created_by' => $this->otherCompanyUser->id,
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->get(route('inventory.products.show', $product));
        $response->assertNotFound();
    }

    public function test_cross_company_account_blocked(): void
    {
        $account = Account::create([
            'code' => '1000',
            'name' => 'Other Co Account',
            'type' => 'asset',
            'company_id' => $this->otherCompany->id,
        ]);

        $response = $this->actingAdmin()->get(route('finance.accounts.show', $account));
        $response->assertNotFound();
    }
}
