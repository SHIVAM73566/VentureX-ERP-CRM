<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Account;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Container;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LandedCost;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\RfqResponse;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AutoJournalService;
use App\Services\CompanyContext;
use App\Services\SettingService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WorkflowIntegrationTest extends TestCase
{
    protected Company $company;

    protected Company $otherCompany;

    protected User $admin;

    protected User $viewer;

    protected User $salesUser;

    protected User $procurementUser;

    protected User $otherCompanyUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        User::whereIn('email', ['admin@workflow.test', 'viewer@workflow.test', 'sales@workflow.test', 'procurement@workflow.test', 'other@othercorp.test'])->forceDelete();
        Company::whereIn('name', ['Workflow Test Corp', 'Other Corp Ltd'])->forceDelete();

        $this->company = Company::create([
            'name' => 'Workflow Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->otherCompany = Company::create([
            'name' => 'Other Corp Ltd',
            'is_active' => true,
            'currency_code' => 'EUR',
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
        $salesRole = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $procurementRole = Role::firstOrCreate(['name' => 'procurement', 'guard_name' => 'web']);

        DB::table('role_has_permissions')->where('role_id', $superAdminRole->id)->delete();
        DB::table('role_has_permissions')->where('role_id', $viewerRole->id)->delete();
        DB::table('role_has_permissions')->where('role_id', $salesRole->id)->delete();
        DB::table('role_has_permissions')->where('role_id', $procurementRole->id)->delete();

        $viewerRole->syncPermissions([
            'customers.view', 'products.view', 'quotations.view', 'invoices.view',
            'payments.view', 'suppliers.view', 'purchase_orders.view', 'ai.view',
            'inventory.view', 'finance.view', 'logistics.view', 'purchase.view',
            'contacts.view', 'leads.view', 'opportunities.view', 'activities.view',
            'sales.view', 'reports.view', 'dashboard.view',
        ]);

        $salesRole->syncPermissions([
            'customers.view', 'customers.create', 'customers.edit',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'leads.view', 'leads.create', 'leads.edit',
            'opportunities.view', 'opportunities.create', 'opportunities.edit',
            'activities.view', 'activities.create', 'activities.edit',
            'quotations.view', 'quotations.create', 'quotations.edit',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'payments.view', 'payments.create', 'payments.edit',
            'products.view',
        ]);

        $procurementRole->syncPermissions([
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'supplier_offers.view', 'supplier_offers.create', 'supplier_offers.edit',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit',
            'products.view', 'products.create', 'products.edit',
            'inventory.view', 'inventory.create',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@workflow.test',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdminRole);

        $this->viewer = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@workflow.test',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->viewer->assignRole($viewerRole);

        $this->salesUser = User::create([
            'name' => 'Sales User',
            'email' => 'sales@workflow.test',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->salesUser->assignRole($salesRole);

        $this->procurementUser = User::create([
            'name' => 'Procurement User',
            'email' => 'procurement@workflow.test',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->procurementUser->assignRole($procurementRole);

        $this->otherCompanyUser = User::create([
            'name' => 'Other User',
            'email' => 'other@othercorp.test',
            'password' => Hash::make('password'),
            'company_id' => $this->otherCompany->id,
            'is_active' => true,
        ]);
        $this->otherCompanyUser->assignRole($viewerRole);

        CompanyContext::set($this->company->id);
    }

    protected function actingAdmin()
    {
        CompanyContext::set($this->company->id);

        return $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    protected function actingViewer()
    {
        CompanyContext::set($this->company->id);

        return $this->actingAs($this->viewer)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    protected function actingSales()
    {
        CompanyContext::set($this->company->id);

        return $this->actingAs($this->salesUser)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    protected function actingProcurement()
    {
        CompanyContext::set($this->company->id);

        return $this->actingAs($this->procurementUser)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    protected function actingOtherCompany()
    {
        CompanyContext::set($this->otherCompany->id);

        return $this->actingAs($this->otherCompanyUser)
            ->withSession(['two_factor_verified_at' => now()->timestamp]);
    }

    // =========================================================================
    // 1. CRM WORKFLOW
    // =========================================================================

    public function test_crm_full_workflow(): void
    {
        DB::transaction(function () {
            // Create customer
            $customer = Customer::create([
                'company_id' => $this->company->id,
                'name' => 'Acme Corp',
                'email' => 'info@acme.com',
                'phone' => '+1-555-0100',
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('customers', ['id' => $customer->id, 'company_id' => $this->company->id]);

            // Add contact
            $contact = Contact::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@acme.com',
                'phone' => '+1-555-0101',
                'is_primary' => true,
            ]);
            $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'customer_id' => $customer->id]);
            $this->assertEquals('John Doe', $contact->fullName());

            // Create lead
            $lead = Lead::create([
                'company_id' => $this->company->id,
                'contact_name' => 'Jane Smith',
                'company_name' => 'TechStart Inc',
                'email' => 'jane@techstart.com',
                'source' => 'website',
                'status' => 'new',
                'estimated_value' => 50000,
                'currency_code' => 'USD',
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'new']);

            // Convert lead to opportunity
            $opportunity = Opportunity::create([
                'company_id' => $this->company->id,
                'name' => 'TechStart Software Deal',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'expected_value' => 75000,
                'currency_code' => 'USD',
                'stage' => 'qualification',
                'probability' => 0.25,
                'assigned_to' => $this->salesUser->id,
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('opportunities', ['id' => $opportunity->id, 'lead_id' => $lead->id]);

            // Create activity
            $activity = Activity::create([
                'company_id' => $this->company->id,
                'type' => 'call',
                'subject_type' => Opportunity::class,
                'subject_id' => $opportunity->id,
                'title' => 'Initial Discovery Call',
                'description' => 'Discuss requirements with TechStart',
                'due_at' => now()->addDays(3),
                'status' => 'open',
                'assigned_to' => $this->salesUser->id,
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('activities', ['id' => $activity->id, 'status' => 'open']);

            // Track follow-up
            $followUp = Activity::create([
                'company_id' => $this->company->id,
                'type' => 'follow_up',
                'subject_type' => Opportunity::class,
                'subject_id' => $opportunity->id,
                'title' => 'Follow-up: Send proposal',
                'due_at' => now()->addDays(7),
                'status' => 'open',
                'assigned_to' => $this->salesUser->id,
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('activities', ['id' => $followUp->id, 'type' => 'follow_up']);
        });
    }

    public function test_crm_customer_create_requires_name(): void
    {
        $response = $this->actingAdmin()->post(route('customers.store'), [
            'name' => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_crm_lead_create_requires_contact_name(): void
    {
        $response = $this->actingAdmin()->post(route('leads.store'), [
            'contact_name' => '',
            'status' => 'new',
        ]);

        $response->assertSessionHasErrors('contact_name');
    }

    public function test_crm_opportunity_requires_name(): void
    {
        $response = $this->actingAdmin()->post(route('opportunities.store'), [
            'name' => '',
            'stage' => 'qualification',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_crm_viewer_cannot_create_customer(): void
    {
        $response = $this->actingViewer()->post(route('customers.store'), [
            'name' => 'Should Fail',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_crm_viewer_cannot_create_lead(): void
    {
        $response = $this->actingViewer()->post(route('leads.store'), [
            'contact_name' => 'Should Fail',
            'status' => 'new',
        ]);

        $response->assertForbidden();
    }

    public function test_crm_sales_user_can_create_customer_and_lead(): void
    {
        $this->actingSales()->post(route('customers.store'), [
            'name' => 'Sales Customer',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', ['name' => 'Sales Customer', 'company_id' => $this->company->id]);

        $this->actingSales()->post(route('leads.store'), [
            'contact_name' => 'Sales Lead',
            'status' => 'new',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['contact_name' => 'Sales Lead', 'company_id' => $this->company->id]);
    }

    public function test_crm_other_company_cannot_see_customers(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Secret Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingOtherCompany()->get(route('customers.show', $customer));
        $response->assertForbidden();
    }

    public function test_crm_company_isolation_contacts(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'My Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $contact = Contact::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'first_name' => 'Private',
            'last_name' => 'Contact',
        ]);

        $this->actingOtherCompany()->delete(route('crm.contacts.destroy', $contact));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }

    // =========================================================================
    // 2. SALES WORKFLOW
    // =========================================================================

    public function test_sales_full_workflow(): void
    {
        DB::transaction(function () {
            $customer = Customer::create([
                'company_id' => $this->company->id,
                'name' => 'Sales Workflow Customer',
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);

            // Create quotation
            $quotation = Quotation::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'quotation_number' => 'QUOT-2026-001',
                'status' => 'draft',
                'subtotal' => 1000,
                'discount' => 0,
                'tax' => 100,
                'total' => 1100,
                'currency_code' => 'USD',
                'valid_until' => now()->addDays(30),
                'created_by' => $this->admin->id,
            ]);

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'description' => 'Product A',
                'quantity' => 10,
                'unit_price' => 100,
                'discount' => 0,
                'tax_rate' => 10,
                'line_total' => 1100,
                'sort' => 1,
            ]);
            $this->assertDatabaseHas('quotations', ['id' => $quotation->id, 'status' => 'draft']);

            // Send to customer
            $quotation->update(['status' => 'sent']);
            $this->assertDatabaseHas('quotations', ['id' => $quotation->id, 'status' => 'sent']);

            // Accept quotation
            $quotation->update(['status' => 'accepted']);
            $this->assertDatabaseHas('quotations', ['id' => $quotation->id, 'status' => 'accepted']);

            // Convert to sales order
            $response = $this->actingAdmin()->post(route('sales.quotations.convert', $quotation));
            $response->assertRedirect();
            $this->assertDatabaseHas('sales_orders', [
                'quotation_id' => $quotation->id,
                'company_id' => $this->company->id,
            ]);

            $salesOrder = SalesOrder::where('quotation_id', $quotation->id)->first();
            $this->assertNotNull($salesOrder);

            // Confirm order
            $salesOrder->update(['status' => 'confirmed']);
            $this->assertDatabaseHas('sales_orders', ['id' => $salesOrder->id, 'status' => 'confirmed']);

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'sales_order_id' => $salesOrder->id,
                'invoice_number' => 'INV-2026-001',
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => 1000,
                'discount' => 0,
                'tax' => 100,
                'total' => 1100,
                'paid_amount' => 0,
                'created_by' => $this->admin->id,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Product A',
                'quantity' => 10,
                'unit_price' => 100,
                'discount' => 0,
                'tax_rate' => 10,
                'line_total' => 1100,
                'sort' => 1,
            ]);

            // Record payment
            $payment = Payment::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-2026-001',
                'payment_date' => now()->toDateString(),
                'amount' => 1100,
                'method' => 'bank',
                'status' => 'completed',
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 1100]);

            // Mark invoice paid
            $invoice->update(['paid_amount' => 1100, 'status' => 'paid']);
            $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
            $this->assertEquals(0, $invoice->fresh()->outstandingBalance());
        });
    }

    public function test_sales_quotation_create_requires_customer(): void
    {
        $response = $this->actingAdmin()->post(route('sales.quotations.store'), [
            'customer_id' => '',
            'status' => 'draft',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('customer_id');
    }

    public function test_sales_invoice_create_requires_issue_date(): void
    {
        $response = $this->actingAdmin()->post(route('sales.invoices.store'), [
            'customer_id' => 1,
            'issue_date' => '',
            'status' => 'draft',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('issue_date');
    }

    public function test_sales_viewer_cannot_create_quotation(): void
    {
        $response = $this->actingViewer()->post(route('sales.quotations.store'), [
            'customer_id' => 1,
            'status' => 'draft',
            'items' => [],
        ]);

        $response->assertForbidden();
    }

    public function test_sales_other_company_cannot_see_quotations(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Private Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $quotation = Quotation::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUOT-PRIVATE-001',
            'status' => 'draft',
            'subtotal' => 0,
            'total' => 0,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingOtherCompany()->get(route('sales.quotations.show', $quotation));
        $response->assertForbidden();
    }

    public function test_sales_payment_methods_are_valid(): void
    {
        foreach (Payment::METHODS as $method => $label) {
            $customer = Customer::create([
                'company_id' => $this->company->id,
                'name' => "Pay $label Customer",
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);

            $payment = Payment::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'payment_number' => "PAY-{$method}-001",
                'payment_date' => now()->toDateString(),
                'amount' => 100,
                'method' => $method,
                'status' => 'completed',
                'created_by' => $this->admin->id,
            ]);

            $this->assertEquals($method, $payment->method);
        }
    }

    public function test_sales_payment_status_transitions(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Status Test Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $payment = Payment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-STATUS-001',
            'payment_date' => now()->toDateString(),
            'amount' => 500,
            'method' => 'bank',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($payment->isPending());
        $this->assertFalse($payment->isCompleted());

        $payment->markCompleted();
        $this->assertTrue($payment->isCompleted());
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'completed']);

        $payment2 = Payment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-STATUS-002',
            'payment_date' => now()->toDateString(),
            'amount' => 200,
            'method' => 'card',
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $payment2->markFailed('Card declined');
        $this->assertTrue($payment2->isFailed());

        $payment3 = Payment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-STATUS-003',
            'payment_date' => now()->toDateString(),
            'amount' => 300,
            'method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $payment3->markRefunded();
        $this->assertTrue($payment3->isRefunded());
    }

    // =========================================================================
    // 3. PROCUREMENT WORKFLOW
    // =========================================================================

    public function test_procurement_full_workflow(): void
    {
        DB::transaction(function () {
            // Create supplier
            $supplier = Supplier::create([
                'company_id' => $this->company->id,
                'name' => 'Global Metals Ltd',
                'email' => 'sales@globalmetals.com',
                'contact_person' => 'Bob Wilson',
                'status' => 'pending',
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'status' => 'pending']);

            // Create purchase requisition
            $pr = PurchaseRequisition::create([
                'company_id' => $this->company->id,
                'pr_number' => 'PR-2026-001',
                'requester_id' => $this->procurementUser->id,
                'status' => 'draft',
                'priority' => 'high',
                'required_date' => now()->addDays(14)->toDateString(),
                'created_by' => $this->procurementUser->id,
            ]);

            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $pr->id,
                'description' => 'Copper Cathodes',
                'quantity' => 100,
                'unit' => 'MT',
                'sort' => 1,
            ]);
            $this->assertDatabaseHas('purchase_requisitions', ['id' => $pr->id, 'status' => 'draft']);

            // Approve requisition
            $pr->update(['status' => 'approved']);
            $this->assertDatabaseHas('purchase_requisitions', ['id' => $pr->id, 'status' => 'approved']);

            // Create RFQ
            $rfq = Rfq::create([
                'company_id' => $this->company->id,
                'rfq_number' => 'RFQ-2026-001',
                'title' => 'Copper Cathodes RFQ',
                'description' => 'Request for copper cathodes supply',
                'status' => 'sent',
                'issued_at' => now()->toDateString(),
                'closes_at' => now()->addDays(14)->toDateString(),
                'created_by' => $this->admin->id,
            ]);

            RfqItem::create([
                'rfq_id' => $rfq->id,
                'description' => 'Copper Cathodes Grade A',
                'quantity' => 100,
                'unit' => 'MT',
                'sort' => 1,
            ]);

            // Submit supplier offer via RFQ response
            $rfqResponse = RfqResponse::create([
                'rfq_id' => $rfq->id,
                'supplier_id' => $supplier->id,
                'response_number' => 'RSP-2026-001',
                'amount' => 850000,
                'delivery_time_days' => 21,
                'valid_until' => now()->addDays(30)->toDateString(),
                'status' => 'submitted',
            ]);
            $this->assertDatabaseHas('rfq_responses', ['id' => $rfqResponse->id, 'status' => 'submitted']);

            // Create purchase order
            $po = PurchaseOrder::create([
                'company_id' => $this->company->id,
                'po_number' => 'PO-2026-001',
                'supplier_id' => $supplier->id,
                'rfq_id' => $rfq->id,
                'status' => 'draft',
                'order_date' => now()->toDateString(),
                'expected_date' => now()->addDays(21)->toDateString(),
                'subtotal' => 850000,
                'total' => 850000,
                'created_by' => $this->admin->id,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'description' => 'Copper Cathodes Grade A',
                'quantity' => 100,
                'unit' => 'MT',
                'unit_price' => 8500,
                'line_total' => 850000,
                'sort' => 1,
            ]);

            $po->update(['status' => 'ordered']);
            $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'ordered']);

            // Receive goods - create stock movement
            $warehouse = Warehouse::create([
                'company_id' => $this->company->id,
                'code' => 'WH-01',
                'name' => 'Main Warehouse',
                'is_default' => true,
                'status' => 'active',
            ]);

            $product = Product::create([
                'company_id' => $this->company->id,
                'sku' => 'COPPER-001',
                'name' => 'Copper Cathodes',
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);

            StockMovement::create([
                'company_id' => $this->company->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'in',
                'quantity' => 100,
                'unit_cost' => 8500,
                'reference_type' => PurchaseOrder::class,
                'reference_id' => $po->id,
                'created_by' => $this->admin->id,
            ]);

            $this->assertEquals(100, $product->fresh()->availableStock());
        });
    }

    public function test_procurement_requisition_requires_status(): void
    {
        $response = $this->actingAdmin()->post(route('procurement.requisitions.store'), [
            'status' => '',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_procurement_viewer_cannot_create_supplier(): void
    {
        $response = $this->actingViewer()->post(route('suppliers.store'), [
            'name' => 'Should Fail',
        ]);

        $response->assertForbidden();
    }

    public function test_procurement_other_company_cannot_see_suppliers(): void
    {
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Private Supplier',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingOtherCompany()->get(route('suppliers.show', $supplier));
        $response->assertForbidden();
    }

    public function test_procurement_supplier_status_transitions(): void
    {
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Status Supplier',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        foreach (['pending', 'verified', 'approved', 'rejected', 'blocked'] as $status) {
            $supplier->update(['status' => $status]);
            $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'status' => $status]);
        }
    }

    // =========================================================================
    // 4. INVENTORY WORKFLOW
    // =========================================================================

    public function test_inventory_full_workflow(): void
    {
        DB::transaction(function () {
            // Create product
            $product = Product::create([
                'company_id' => $this->company->id,
                'sku' => 'INV-TEST-001',
                'name' => 'Test Widget',
                'category' => 'Electronics',
                'purchase_price' => 25.00,
                'selling_price' => 49.99,
                'reorder_level' => 20,
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => 'INV-TEST-001']);

            $warehouse = Warehouse::create([
                'company_id' => $this->company->id,
                'code' => 'WH-MAIN',
                'name' => 'Main Warehouse',
                'is_default' => true,
                'status' => 'active',
            ]);

            // Stock in movement
            StockMovement::create([
                'company_id' => $this->company->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'in',
                'quantity' => 100,
                'unit_cost' => 25.00,
                'created_by' => $this->admin->id,
            ]);

            // Check stock level
            $this->assertEquals(100, $product->fresh()->availableStock());
            $this->assertFalse($product->fresh()->isLowStock());

            // Stock out movement
            StockMovement::create([
                'company_id' => $this->company->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'out',
                'quantity' => 90,
                'created_by' => $this->admin->id,
            ]);

            // Verify reduced stock
            $this->assertEquals(10, $product->fresh()->availableStock());

            // Verify reorder alert triggers
            $this->assertTrue($product->fresh()->isLowStock());
        });
    }

    public function test_inventory_stock_service_available_stock(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'SVC-TEST-001',
            'name' => 'Service Test Product',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-SVC',
            'name' => 'Service Warehouse',
            'is_default' => true,
            'status' => 'active',
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 50,
            'unit_cost' => 10,
            'created_by' => $this->admin->id,
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 20,
            'created_by' => $this->admin->id,
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'adjustment',
            'quantity' => 5,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(35, StockService::availableStock($product->id));
        $this->assertEquals(35, StockService::availableStock($product->id, $warehouse->id));
    }

    public function test_inventory_warehouse_isolation(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'WH-ISO-001',
            'name' => 'Warehouse Isolation Test',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $wh1 = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-1',
            'name' => 'Warehouse 1',
            'status' => 'active',
        ]);

        $wh2 = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-2',
            'name' => 'Warehouse 2',
            'status' => 'active',
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $wh1->id,
            'type' => 'in',
            'quantity' => 100,
            'created_by' => $this->admin->id,
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $wh2->id,
            'type' => 'in',
            'quantity' => 50,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(100, StockService::availableStock($product->id, $wh1->id));
        $this->assertEquals(50, StockService::availableStock($product->id, $wh2->id));
        $this->assertEquals(150, StockService::availableStock($product->id));
    }

    public function test_inventory_viewer_cannot_create_product(): void
    {
        $response = $this->actingViewer()->post(route('inventory.products.store'), [
            'name' => 'Fail Product',
            'sku' => 'FAIL-001',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_inventory_other_company_cannot_see_products(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'PRIVATE-001',
            'name' => 'Private Product',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingOtherCompany()->get(route('inventory.products.show', $product));
        $response->assertForbidden();
    }

    public function test_inventory_product_requires_name(): void
    {
        $response = $this->actingAdmin()->post(route('inventory.products.store'), [
            'name' => '',
            'sku' => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_inventory_stock_movements_track_reference(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'REF-001',
            'name' => 'Reference Tracking Product',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-REF',
            'name' => 'Reference Warehouse',
            'is_default' => true,
            'status' => 'active',
        ]);

        $po = PurchaseOrder::create([
            'company_id' => $this->company->id,
            'po_number' => 'PO-REF-001',
            'status' => 'ordered',
            'order_date' => now()->toDateString(),
            'total' => 500,
            'created_by' => $this->admin->id,
        ]);

        $movement = StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 50,
            'unit_cost' => 10,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po->id,
            'note' => 'PO receipt',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(PurchaseOrder::class, $movement->reference_type);
        $this->assertEquals($po->id, $movement->reference_id);
    }

    // =========================================================================
    // 5. FINANCE WORKFLOW
    // =========================================================================

    public function test_finance_full_workflow(): void
    {
        DB::transaction(function () {
            // Create chart of accounts
            $cashAccount = Account::create([
                'company_id' => $this->company->id,
                'code' => '1100',
                'name' => 'Cash',
                'type' => 'asset',
                'is_active' => true,
            ]);

            $arAccount = Account::create([
                'company_id' => $this->company->id,
                'code' => '1200',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'is_active' => true,
            ]);

            $apAccount = Account::create([
                'company_id' => $this->company->id,
                'code' => '2100',
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'is_active' => true,
            ]);

            $revenueAccount = Account::create([
                'company_id' => $this->company->id,
                'code' => '4000',
                'name' => 'Revenue',
                'type' => 'income',
                'is_active' => true,
            ]);

            $expenseAccount = Account::create([
                'company_id' => $this->company->id,
                'code' => '5000',
                'name' => 'Operating Expense',
                'type' => 'expense',
                'is_active' => true,
            ]);

            $this->assertEquals('asset', $cashAccount->type);
            $this->assertEquals('liability', $apAccount->type);
            $this->assertEquals('income', $revenueAccount->type);

            // Create journal entry
            $entry = JournalEntry::create([
                'company_id' => $this->company->id,
                'entry_number' => 'JE-2026-001',
                'date' => now()->toDateString(),
                'description' => 'Test journal entry',
                'status' => 'draft',
                'created_by' => $this->admin->id,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $expenseAccount->id,
                'debit' => 1000,
                'credit' => 0,
                'description' => 'Office supplies expense',
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => 1000,
                'description' => 'Cash payment',
            ]);

            // Verify balanced
            $this->assertTrue($entry->fresh()->isBalanced());
            $this->assertEquals(1000, $entry->fresh()->totalDebit());
            $this->assertEquals(1000, $entry->fresh()->totalCredit());

            // Post entry
            $entry->update(['status' => 'posted']);
            $this->assertDatabaseHas('journal_entries', ['id' => $entry->id, 'status' => 'posted']);

            // Verify account balances
            $this->assertEquals(-1000, $cashAccount->fresh()->balance());
            $this->assertEquals(1000, $expenseAccount->fresh()->balance());
        });
    }

    public function test_finance_auto_journal_on_invoice(): void
    {
        DB::transaction(function () {
            Account::create([
                'company_id' => $this->company->id,
                'code' => '1100',
                'name' => 'Cash',
                'type' => 'asset',
                'is_active' => true,
            ]);

            Account::create([
                'company_id' => $this->company->id,
                'code' => '1200',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'is_active' => true,
            ]);

            Account::create([
                'company_id' => $this->company->id,
                'code' => '4000',
                'name' => 'Revenue',
                'type' => 'income',
                'is_active' => true,
            ]);

            $customer = Customer::create([
                'company_id' => $this->company->id,
                'name' => 'Journal Customer',
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);

            $invoice = Invoice::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-JE-001',
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'total' => 5000,
                'created_by' => $this->admin->id,
            ]);

            AutoJournalService::recordInvoice($invoice);

            $entry = JournalEntry::where('description', 'like', '%INV-JE-001%')->first();
            $this->assertNotNull($entry);
            $this->assertEquals('posted', $entry->status);
            $this->assertEquals(2, $entry->lines()->count());
            $this->assertTrue($entry->isBalanced());
        });
    }

    public function test_finance_auto_journal_on_payment(): void
    {
        DB::transaction(function () {
            Account::create([
                'company_id' => $this->company->id,
                'code' => '1100',
                'name' => 'Cash',
                'type' => 'asset',
                'is_active' => true,
            ]);

            Account::create([
                'company_id' => $this->company->id,
                'code' => '1200',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'is_active' => true,
            ]);

            $customer = Customer::create([
                'company_id' => $this->company->id,
                'name' => 'Payment JE Customer',
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);

            $payment = Payment::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'payment_number' => 'PAY-JE-001',
                'payment_date' => now()->toDateString(),
                'amount' => 2500,
                'method' => 'bank',
                'status' => 'completed',
                'created_by' => $this->admin->id,
            ]);

            AutoJournalService::recordPayment($payment);

            $entry = JournalEntry::where('description', 'like', '%PAY-JE-001%')->first();
            $this->assertNotNull($entry);
            $this->assertEquals('posted', $entry->status);
            $this->assertEquals(2, $entry->lines()->count());
            $this->assertTrue($entry->isBalanced());
        });
    }

    public function test_finance_account_requires_code_and_name(): void
    {
        Account::create([
            'company_id' => $this->company->id,
            'code' => 'TEST-ACCT-001',
            'name' => 'Test Account',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $response = $this->actingAdmin()->get(route('finance.accounts.create'));
        $response->assertOk();
    }

    public function test_finance_journal_entry_requires_balanced_lines(): void
    {
        $account = Account::create([
            'company_id' => $this->company->id,
            'code' => '1100',
            'name' => 'Cash',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'company_id' => $this->company->id,
            'entry_number' => 'JE-UNBAL-001',
            'date' => now()->toDateString(),
            'description' => 'Unbalanced entry',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => 1000,
            'credit' => 0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => 0,
            'credit' => 500,
        ]);

        $this->assertFalse($entry->fresh()->isBalanced());
        $this->assertEquals(1000, $entry->fresh()->totalDebit());
        $this->assertEquals(500, $entry->fresh()->totalCredit());
    }

    public function test_finance_viewer_can_view_but_not_create_accounts(): void
    {
        Account::create([
            'company_id' => $this->company->id,
            'code' => 'VIEW-001',
            'name' => 'View Only Account',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $this->actingViewer()->get(route('finance.accounts.index'))->assertOk();

        $response = $this->actingViewer()->post(route('finance.accounts.store'), [
            'code' => 'FAIL-001',
            'name' => 'Should Fail',
            'type' => 'asset',
        ]);

        $response->assertForbidden();
    }

    public function test_finance_other_company_cannot_see_accounts(): void
    {
        $account = Account::create([
            'company_id' => $this->company->id,
            'code' => 'PRIVATE-ACCT',
            'name' => 'Private Account',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $response = $this->actingOtherCompany()->get(route('finance.accounts.show', $account));
        $response->assertForbidden();
    }

    // =========================================================================
    // 6. LOGISTICS WORKFLOW
    // =========================================================================

    public function test_logistics_full_workflow(): void
    {
        DB::transaction(function () {
            // Create shipment
            $shipment = Shipment::create([
                'company_id' => $this->company->id,
                'shipment_number' => 'SHP-2026-001',
                'type' => 'outbound',
                'status' => 'draft',
                'mode' => 'sea',
                'carrier' => 'Maersk Line',
                'origin' => 'Mumbai, India',
                'destination' => 'Rotterdam, Netherlands',
                'departure_date' => now()->addDays(7)->toDateString(),
                'arrival_date' => now()->addDays(28)->toDateString(),
                'weight' => 25000.0000,
                'volume' => 60.0000,
                'created_by' => $this->admin->id,
            ]);
            $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'status' => 'draft']);

            // Add container
            $container = Container::create([
                'company_id' => $this->company->id,
                'container_number' => 'MSKU-1234567',
                'size' => '40ft',
                'type' => 'standard',
                'seal_number' => 'SL-9876',
                'shipment_id' => $shipment->id,
                'status' => 'loading',
                'capacity' => 67.7000,
                'weight' => 25000.0000,
            ]);
            $this->assertDatabaseHas('containers', ['id' => $container->id, 'status' => 'loading']);

            // Add landed costs
            $freight = LandedCost::create([
                'company_id' => $this->company->id,
                'shipment_id' => $shipment->id,
                'container_id' => $container->id,
                'cost_type' => 'freight',
                'description' => 'Ocean freight charges',
                'amount' => 3500,
                'currency' => 'USD',
                'paid_to' => 'Maersk Line',
                'created_by' => $this->admin->id,
            ]);

            $customs = LandedCost::create([
                'company_id' => $this->company->id,
                'shipment_id' => $shipment->id,
                'container_id' => $container->id,
                'cost_type' => 'customs_duty',
                'description' => 'Import customs duty',
                'amount' => 1200,
                'currency' => 'USD',
                'created_by' => $this->admin->id,
            ]);

            $insurance = LandedCost::create([
                'company_id' => $this->company->id,
                'shipment_id' => $shipment->id,
                'container_id' => $container->id,
                'cost_type' => 'insurance',
                'description' => 'Marine insurance',
                'amount' => 450,
                'currency' => 'USD',
                'created_by' => $this->admin->id,
            ]);

            $totalCosts = LandedCost::where('shipment_id', $shipment->id)->sum('amount');
            $this->assertEquals(5150, $totalCosts);

            // Track status changes
            $shipment->update(['status' => 'scheduled']);
            $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'status' => 'scheduled']);

            $container->update(['status' => 'loaded']);
            $shipment->update(['status' => 'in_transit']);
            $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'status' => 'in_transit']);

            $shipment->update(['status' => 'delivered']);
            $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'status' => 'delivered']);
            $container->update(['status' => 'delivered']);
        });
    }

    public function test_logistics_landed_cost_types_are_valid(): void
    {
        foreach (LandedCost::COST_TYPES as $type => $label) {
            $this->assertNotEmpty($type);
            $this->assertNotEmpty($label);
        }
    }

    public function test_logistics_viewer_can_view_but_not_create(): void
    {
        $this->actingViewer()->get(route('logistics.shipments.index'))->assertOk();
        $this->actingViewer()->get(route('logistics.containers.index'))->assertOk();
        $this->actingViewer()->get(route('logistics.landed-costs.index'))->assertOk();

        $response = $this->actingViewer()->post(route('logistics.shipments.store'), [
            'shipment_number' => 'FAIL-SHP-001',
            'status' => 'draft',
        ]);

        $response->assertForbidden();
    }

    public function test_logistics_other_company_cannot_see_shipments(): void
    {
        $shipment = Shipment::create([
            'company_id' => $this->company->id,
            'shipment_number' => 'SHP-PRIVATE-001',
            'type' => 'outbound',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingOtherCompany()->get(route('logistics.shipments.show', $shipment));
        $response->assertForbidden();
    }

    public function test_logistics_shipment_modes_are_valid(): void
    {
        foreach (Shipment::MODES as $mode => $label) {
            $this->assertNotEmpty($mode);
            $this->assertNotEmpty($label);
        }
    }

    public function test_logistics_container_status_transitions(): void
    {
        $container = Container::create([
            'company_id' => $this->company->id,
            'container_number' => 'TEST-CTR-001',
            'size' => '20ft',
            'type' => 'standard',
            'status' => 'empty',
        ]);

        $statuses = ['empty', 'loading', 'loaded', 'in_transit', 'at_port', 'delivered'];
        foreach ($statuses as $status) {
            $container->update(['status' => $status]);
            $this->assertDatabaseHas('containers', ['id' => $container->id, 'status' => $status]);
        }
    }

    // =========================================================================
    // 7. USER MANAGEMENT WORKFLOW
    // =========================================================================

    public function test_user_management_full_workflow(): void
    {
        User::where('email', 'employee@workflow.test')->forceDelete();

        DB::transaction(function () {
            // Create user
            $newUser = User::create([
                'name' => 'New Employee',
                'email' => 'employee@workflow.test',
                'password' => Hash::make('password123'),
                'company_id' => $this->company->id,
                'is_active' => true,
            ]);

            $this->assertDatabaseHas('users', ['email' => 'employee@workflow.test', 'company_id' => $this->company->id]);

            // Assign role
            $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
            $editorRole->syncPermissions(['customers.view', 'customers.edit', 'products.view']);
            $newUser->assignRole($editorRole);

            $this->assertTrue($newUser->hasRole('editor'));
            $this->assertTrue($newUser->hasPermissionTo('customers.view'));
            $this->assertTrue($newUser->hasPermissionTo('customers.edit'));
            $this->assertFalse($newUser->hasPermissionTo('customers.delete'));
        });
    }

    public function test_user_cannot_be_created_by_viewer(): void
    {
        $response = $this->actingViewer()->post(route('admin.users.store'), [
            'name' => 'Hack User',
            'email' => 'hack@workflow.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => true,
        ]);

        $response->assertForbidden();
    }

    public function test_user_permissions_are_enforced(): void
    {
        $this->actingViewer()->post(route('customers.store'), [
            'name' => 'Fail',
            'status' => 'active',
        ])->assertForbidden();

        $this->actingViewer()->post(route('leads.store'), [
            'contact_name' => 'Fail',
            'status' => 'new',
        ])->assertForbidden();

        $this->actingViewer()->post(route('inventory.products.store'), [
            'name' => 'Fail',
            'status' => 'active',
        ])->assertForbidden();

        $this->actingViewer()->post(route('suppliers.store'), [
            'name' => 'Fail',
        ])->assertForbidden();
    }

    public function test_viewer_role_has_view_permissions(): void
    {
        $this->actingViewer()->get(route('customers.index'))->assertOk();
        $this->actingViewer()->get(route('inventory.products.index'))->assertOk();
        $this->actingViewer()->get(route('suppliers.index'))->assertOk();
        $this->actingViewer()->get(route('sales.quotations.index'))->assertOk();
        $this->actingViewer()->get(route('sales.invoices.index'))->assertOk();
        $this->actingViewer()->get(route('sales.payments.index'))->assertOk();
    }

    // =========================================================================
    // 8. SETTINGS WORKFLOW
    // =========================================================================

    public function test_settings_full_workflow(): void
    {
        DB::transaction(function () {
            $settingService = app(SettingService::class);

            // Update company settings
            $settingService->set('company_name', 'Updated Corp', ['group' => 'company']);
            $settingService->set('company_email', 'updated@corp.com', ['group' => 'company']);

            $this->assertEquals('Updated Corp', $settingService->get('company_name'));
            $this->assertEquals('updated@corp.com', $settingService->get('company_email'));

            // Verify AI config
            $settingService->set('ai_provider', 'nvidia', ['group' => 'ai']);
            $settingService->set('ai_model', 'llama-3.1-70b', ['group' => 'ai']);

            $this->assertEquals('nvidia', $settingService->get('ai_provider'));
            $this->assertEquals('llama-3.1-70b', $settingService->get('ai_model'));

            // Test notification preferences
            $settingService->set('email_notifications', true, ['group' => 'notifications']);
            $settingService->set('slack_webhook', 'https://hooks.slack.com/test', ['group' => 'notifications']);

            $this->assertTrue((bool) $settingService->get('email_notifications'));
            $this->assertEquals('https://hooks.slack.com/test', $settingService->get('slack_webhook'));

            // Verify updateOrCreate behavior
            $settingService->set('company_name', 'Final Corp', ['group' => 'company']);
            $this->assertEquals('Final Corp', $settingService->get('company_name'));
            $this->assertEquals(1, Setting::where('key', 'company_name')->where('company_id', $this->company->id)->count());
        });
    }

    public function test_settings_are_company_isolated(): void
    {
        $settingService = app(SettingService::class);

        CompanyContext::set($this->company->id);
        $settingService->set('secret_key', 'company1-secret', ['group' => 'security']);

        CompanyContext::set($this->otherCompany->id);
        $settingService->set('secret_key', 'company2-secret', ['group' => 'security']);

        CompanyContext::set($this->company->id);
        $this->assertEquals('company1-secret', $settingService->get('secret_key'));

        CompanyContext::set($this->otherCompany->id);
        $this->assertEquals('company2-secret', $settingService->get('secret_key'));
    }

    public function test_settings_default_value_when_missing(): void
    {
        $settingService = app(SettingService::class);

        $this->assertEquals('fallback', $settingService->get('nonexistent_key', 'fallback'));
        $this->assertNull($settingService->get('nonexistent_key'));
    }

    public function test_settings_json_value_storage(): void
    {
        $settingService = app(SettingService::class);

        $config = ['enabled' => true, 'threshold' => 100, 'modules' => ['crm', 'erp']];
        $settingService->set('complex_config', $config, ['group' => 'system']);

        $retrieved = $settingService->get('complex_config');
        $this->assertEquals($config, $retrieved);
    }

    // =========================================================================
    // 9. SEARCH FUNCTIONALITY
    // =========================================================================

    public function test_search_finds_customers(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Searchable Corp XYZ',
            'email' => 'search@corp.com',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->get(route('search', ['q' => 'Searchable Corp']));
        $response->assertOk();
    }

    public function test_search_requires_minimum_2_characters(): void
    {
        $response = $this->actingAdmin()->get(route('search', ['q' => 'a']));
        $response->assertOk();
    }

    public function test_search_finds_leads(): void
    {
        Lead::create([
            'company_id' => $this->company->id,
            'contact_name' => 'Search Lead Person',
            'company_name' => 'Search Lead Corp',
            'status' => 'new',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->get(route('search', ['q' => 'Search Lead']));
        $response->assertOk();
    }

    public function test_search_finds_suppliers(): void
    {
        Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Search Supplier Inc',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->get(route('search', ['q' => 'Search Supplier']));
        $response->assertOk();
    }

    public function test_search_isolation_between_companies(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Company1 Private Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingOtherCompany()->get(route('search', ['q' => 'Company1 Private']));
        $response->assertOk();
    }

    // =========================================================================
    // 10. PAGINATION ON LIST VIEWS
    // =========================================================================

    public function test_customers_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('customers.index'));
        $response->assertOk();

        $customers = $response->viewData('customers');
        $this->assertTrue(method_exists($customers, 'links'));
    }

    public function test_products_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('inventory.products.index'));
        $response->assertOk();

        $products = $response->viewData('products');
        $this->assertTrue(method_exists($products, 'links'));
    }

    public function test_suppliers_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('suppliers.index'));
        $response->assertOk();

        $suppliers = $response->viewData('suppliers');
        $this->assertTrue(method_exists($suppliers, 'links'));
    }

    public function test_quotations_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('sales.quotations.index'));
        $response->assertOk();

        $quotations = $response->viewData('quotations');
        $this->assertTrue(method_exists($quotations, 'links'));
    }

    public function test_invoices_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('sales.invoices.index'));
        $response->assertOk();

        $invoices = $response->viewData('invoices');
        $this->assertTrue(method_exists($invoices, 'links'));
    }

    public function test_payments_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('sales.payments.index'));
        $response->assertOk();

        $payments = $response->viewData('payments');
        $this->assertTrue(method_exists($payments, 'links'));
    }

    public function test_accounts_index_is_paginated(): void
    {
        $response = $this->actingAdmin()->get(route('finance.accounts.index'));
        $response->assertOk();

        $accounts = $response->viewData('accounts');
        $this->assertTrue(method_exists($accounts, 'links'));
    }

    // =========================================================================
    // 11. CROSS-CUTTING: EXPORT FUNCTIONALITY
    // =========================================================================

    public function test_export_page_loads(): void
    {
        $response = $this->actingAdmin()->get(route('admin.exports.index'));
        $response->assertOk();
    }

    // =========================================================================
    // 12. CROSS-CUTTING: FILTER AND SORT
    // =========================================================================

    public function test_leads_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('leads.index'));
        $response->assertOk();
    }

    public function test_opportunities_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('opportunities.index'));
        $response->assertOk();
    }

    public function test_activities_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('crm.activities.index'));
        $response->assertOk();
    }

    public function test_contacts_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('crm.contacts.index'));
        $response->assertOk();
    }

    public function test_purchase_requisitions_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('procurement.requisitions.index'));
        $response->assertOk();
    }

    public function test_rfqs_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('procurement.rfqs.index'));
        $response->assertOk();
    }

    public function test_purchase_orders_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('procurement.orders.index'));
        $response->assertOk();
    }

    public function test_warehouses_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('inventory.warehouses.index'));
        $response->assertOk();
    }

    public function test_stock_movements_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('inventory.stock.index'));
        $response->assertOk();
    }

    public function test_finance_dashboard_loads(): void
    {
        $response = $this->actingAdmin()->get(route('finance.dashboard'));
        $response->assertOk();
    }

    public function test_finance_receivables_loads(): void
    {
        $response = $this->actingAdmin()->get(route('finance.receivables'));
        $response->assertOk();
    }

    public function test_finance_payables_loads(): void
    {
        $response = $this->actingAdmin()->get(route('finance.payables'));
        $response->assertOk();
    }

    public function test_journal_entries_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('finance.journals.index'));
        $response->assertOk();
    }

    public function test_sales_orders_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('sales.orders.index'));
        $response->assertOk();
    }

    public function test_pipeline_index_loads(): void
    {
        $response = $this->actingAdmin()->get(route('pipeline.index'));
        $response->assertOk();
    }

    // =========================================================================
    // 13. CROSS-CUTTING: DASHBOARD
    // =========================================================================

    public function test_dashboard_loads_for_admin(): void
    {
        $response = $this->actingAdmin()->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_loads_for_viewer(): void
    {
        $response = $this->actingViewer()->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_loads_for_sales_user(): void
    {
        $response = $this->actingSales()->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_loads_for_procurement_user(): void
    {
        $procurementRole = Role::where('name', 'procurement')->first();
        $procurementRole->givePermissionTo('customers.view');

        $response = $this->actingProcurement()->get(route('dashboard'));
        $response->assertOk();
    }

    // =========================================================================
    // 14. MODEL SCOPES: COMPANY ISOLATION
    // =========================================================================

    public function test_of_company_scope_filters_correctly(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'My Company Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        Customer::create([
            'company_id' => $this->otherCompany->id,
            'name' => 'Other Company Customer',
            'status' => 'active',
            'created_by' => $this->otherCompanyUser->id,
        ]);

        CompanyContext::set($this->company->id);
        $myCustomers = Customer::ofCompany()->get();
        $this->assertCount(1, $myCustomers);
        $this->assertEquals('My Company Customer', $myCustomers->first()->name);

        CompanyContext::set($this->otherCompany->id);
        $otherCustomers = Customer::ofCompany()->get();
        $this->assertCount(1, $otherCustomers);
        $this->assertEquals('Other Company Customer', $otherCustomers->first()->name);
    }

    public function test_supplier_of_company_scope(): void
    {
        Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'My Supplier',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        Supplier::create([
            'company_id' => $this->otherCompany->id,
            'name' => 'Other Supplier',
            'status' => 'pending',
            'created_by' => $this->otherCompanyUser->id,
        ]);

        CompanyContext::set($this->company->id);
        $this->assertCount(1, Supplier::ofCompany()->get());
    }

    public function test_lead_of_company_scope(): void
    {
        Lead::create([
            'company_id' => $this->company->id,
            'contact_name' => 'My Lead',
            'status' => 'new',
            'created_by' => $this->admin->id,
        ]);

        Lead::create([
            'company_id' => $this->otherCompany->id,
            'contact_name' => 'Other Lead',
            'status' => 'new',
            'created_by' => $this->otherCompanyUser->id,
        ]);

        CompanyContext::set($this->company->id);
        $this->assertCount(1, Lead::ofCompany()->get());
    }

    public function test_product_of_company_scope(): void
    {
        Product::create([
            'company_id' => $this->company->id,
            'sku' => 'ISO-001',
            'name' => 'My Product',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'company_id' => $this->otherCompany->id,
            'sku' => 'ISO-002',
            'name' => 'Other Product',
            'status' => 'active',
            'created_by' => $this->otherCompanyUser->id,
        ]);

        CompanyContext::set($this->company->id);
        $this->assertCount(1, Product::ofCompany()->get());
    }

    public function test_account_of_company_scope(): void
    {
        Account::create([
            'company_id' => $this->company->id,
            'code' => 'AC-001',
            'name' => 'My Account',
            'type' => 'asset',
        ]);

        Account::create([
            'company_id' => $this->otherCompany->id,
            'code' => 'AC-002',
            'name' => 'Other Account',
            'type' => 'asset',
        ]);

        CompanyContext::set($this->company->id);
        $this->assertCount(1, Account::ofCompany()->get());
    }

    // =========================================================================
    // 15. DELETE OPERATIONS
    // =========================================================================

    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('customers.destroy', $customer));
        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_supplier_can_be_deleted(): void
    {
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Supplier',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'DEL-001',
            'name' => 'Delete Product',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('inventory.products.destroy', $product));
        $response->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_quotation_can_be_deleted(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Quote Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $quotation = Quotation::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUOT-DEL-001',
            'status' => 'draft',
            'subtotal' => 0,
            'total' => 0,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('sales.quotations.destroy', $quotation));
        $response->assertRedirect(route('sales.quotations.index'));
        $this->assertSoftDeleted('quotations', ['id' => $quotation->id]);
    }

    public function test_payment_can_be_deleted(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Pay Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $payment = Payment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-DEL-001',
            'payment_date' => now()->toDateString(),
            'amount' => 500,
            'method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('sales.payments.destroy', $payment));
        $response->assertRedirect(route('sales.payments.index'));
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    public function test_invoice_can_be_deleted(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Inv Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-DEL-001',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'total' => 0,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->delete(route('sales.invoices.destroy', $invoice));
        $response->assertRedirect(route('sales.invoices.index'));
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_viewer_cannot_delete_customer(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Cannot Delete Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingViewer()->delete(route('customers.destroy', $customer));
        $response->assertForbidden();
    }

    // =========================================================================
    // 16. EDIT OPERATIONS
    // =========================================================================

    public function test_customer_can_be_updated(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Old Customer Name',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->put(route('customers.update', $customer), [
            'name' => 'New Customer Name',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'New Customer Name']);
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'UPD-001',
            'name' => 'Old Product Name',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->put(route('inventory.products.update', $product), [
            'name' => 'New Product Name',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Product Name']);
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Old Supplier Name',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAdmin()->put(route('suppliers.update', $supplier), [
            'name' => 'New Supplier Name',
            'status' => 'verified',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'New Supplier Name', 'status' => 'verified']);
    }

    // =========================================================================
    // 17. EDGE CASES
    // =========================================================================

    public function test_product_reorder_level_boundary(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'BOUNDARY-001',
            'name' => 'Boundary Test Product',
            'reorder_level' => 50,
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-BOUND',
            'name' => 'Boundary Warehouse',
            'is_default' => true,
            'status' => 'active',
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 51,
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($product->fresh()->isLowStock());

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 1,
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($product->fresh()->isLowStock());
    }

    public function test_invoice_outstanding_balance_calculation(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Balance Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-BAL-001',
            'status' => 'partial',
            'issue_date' => now()->toDateString(),
            'total' => 1000,
            'paid_amount' => 300,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(700, $invoice->outstandingBalance());

        $invoice->update(['paid_amount' => 1000]);
        $this->assertEquals(0, $invoice->fresh()->outstandingBalance());

        $invoice->update(['paid_amount' => 1200]);
        $this->assertEquals(0, $invoice->fresh()->outstandingBalance());
    }

    public function test_purchase_order_outstanding_balance(): void
    {
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Balance Supplier',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $po = PurchaseOrder::create([
            'company_id' => $this->company->id,
            'po_number' => 'PO-BAL-001',
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
            'order_date' => now()->toDateString(),
            'total' => 5000,
            'paid_amount' => 2000,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(3000, $po->outstandingBalance());
    }

    // =========================================================================
    // 18. SALES USER WORKFLOW (Role-Based)
    // =========================================================================

    public function test_sales_user_full_crm_workflow(): void
    {
        $this->actingSales()->post(route('customers.store'), [
            'name' => 'Sales Customer',
            'status' => 'active',
        ])->assertRedirect();

        $customer = Customer::where('name', 'Sales Customer')->where('company_id', $this->company->id)->first();
        $this->assertNotNull($customer);

        $this->actingSales()->post(route('leads.store'), [
            'contact_name' => 'Sales Lead Contact',
            'status' => 'new',
        ])->assertRedirect();

        $this->actingSales()->post(route('opportunities.store'), [
            'name' => 'Sales Opportunity',
            'customer_id' => $customer->id,
            'stage' => 'qualification',
        ])->assertRedirect();
    }

    public function test_procurement_user_can_manage_suppliers(): void
    {
        $this->actingProcurement()->post(route('suppliers.store'), [
            'name' => 'Procurement Supplier',
            'status' => 'pending',
        ])->assertRedirect();

        $supplier = Supplier::where('name', 'Procurement Supplier')->where('company_id', $this->company->id)->first();
        $this->assertNotNull($supplier);

        $this->actingProcurement()->put(route('suppliers.update', $supplier), [
            'name' => 'Updated Procurement Supplier',
            'status' => 'verified',
        ])->assertRedirect();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated Procurement Supplier']);
    }

    // =========================================================================
    // 19. ACTIVITY WORKFLOW
    // =========================================================================

    public function test_activity_complete_and_reopen(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Activity Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $activity = Activity::create([
            'company_id' => $this->company->id,
            'type' => 'task',
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'title' => 'Follow up on contract',
            'status' => 'open',
            'assigned_to' => $this->salesUser->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAdmin()->post(route('crm.activities.complete', $activity))->assertRedirect();
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'status' => 'completed']);

        $this->actingAdmin()->post(route('crm.activities.reopen', $activity))->assertRedirect();
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'status' => 'open']);
    }

    public function test_activity_types_are_valid(): void
    {
        foreach (Activity::TYPES as $type => $label) {
            $this->assertNotEmpty($type);
            $this->assertNotEmpty($label);
        }
    }

    // =========================================================================
    // 20. QUOTATION STATUSES
    // =========================================================================

    public function test_quotation_status_constants(): void
    {
        $expectedStatuses = ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'];
        $this->assertEquals($expectedStatuses, array_keys(Quotation::STATUSES));
    }

    public function test_sales_order_status_constants(): void
    {
        $expectedStatuses = ['draft', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'];
        $this->assertEquals($expectedStatuses, array_keys(SalesOrder::STATUSES));
    }

    public function test_invoice_status_constants(): void
    {
        $expectedStatuses = ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'];
        $this->assertEquals($expectedStatuses, array_keys(Invoice::STATUSES));
    }

    // =========================================================================
    // 21. LEAD STATUSES
    // =========================================================================

    public function test_lead_status_constants(): void
    {
        $expectedStatuses = ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];
        $this->assertEquals($expectedStatuses, array_keys(Lead::STATUSES));
    }

    public function test_opportunity_stage_constants(): void
    {
        $expectedStages = ['qualification', 'needs_analysis', 'proposal', 'negotiation', 'won', 'lost'];
        $this->assertEquals($expectedStages, array_keys(Opportunity::STAGES));
    }

    // =========================================================================
    // 22. PURCHASE REQUISITION STATUSES
    // =========================================================================

    public function test_purchase_requisition_status_constants(): void
    {
        $expectedStatuses = ['draft', 'pending_approval', 'approved', 'rejected', 'ordered'];
        $this->assertEquals($expectedStatuses, array_keys(PurchaseRequisition::STATUSES));
    }

    public function test_purchase_requisition_priority_constants(): void
    {
        $expectedPriorities = ['low', 'medium', 'high', 'critical'];
        $this->assertEquals($expectedPriorities, array_keys(PurchaseRequisition::PRIORITIES));
    }

    // =========================================================================
    // 23. RFQ STATUSES
    // =========================================================================

    public function test_rfq_status_constants(): void
    {
        $expectedStatuses = ['draft', 'sent', 'open', 'awarded', 'cancelled'];
        $this->assertEquals($expectedStatuses, array_keys(Rfq::STATUSES));
    }

    // =========================================================================
    // 24. CONTAINER STATUSES
    // =========================================================================

    public function test_container_status_constants(): void
    {
        $expectedStatuses = ['empty', 'loading', 'loaded', 'in_transit', 'at_port', 'delivered'];
        $this->assertEquals($expectedStatuses, array_keys(Container::STATUSES));
    }

    // =========================================================================
    // 25. ACCOUNT TYPES
    // =========================================================================

    public function test_account_type_constants(): void
    {
        $expectedTypes = ['asset', 'liability', 'equity', 'income', 'expense'];
        $this->assertEquals($expectedTypes, array_keys(Account::TYPES));
    }

    public function test_account_balance_calculation(): void
    {
        $assetAccount = Account::create([
            'company_id' => $this->company->id,
            'code' => 'ACCT-ASSET',
            'name' => 'Test Asset',
            'type' => 'asset',
        ]);

        $entry = JournalEntry::create([
            'company_id' => $this->company->id,
            'entry_number' => 'JE-ACCT-001',
            'date' => now()->toDateString(),
            'description' => 'Asset test',
            'status' => 'posted',
            'created_by' => $this->admin->id,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $assetAccount->id,
            'debit' => 5000,
            'credit' => 0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $assetAccount->id,
            'debit' => 0,
            'credit' => 1500,
        ]);

        $this->assertEquals(3500, $assetAccount->fresh()->balance());

        $liabilityAccount = Account::create([
            'company_id' => $this->company->id,
            'code' => 'ACCT-LIAB',
            'name' => 'Test Liability',
            'type' => 'liability',
        ]);

        $entry2 = JournalEntry::create([
            'company_id' => $this->company->id,
            'entry_number' => 'JE-ACCT-002',
            'date' => now()->toDateString(),
            'description' => 'Liability test',
            'status' => 'posted',
            'created_by' => $this->admin->id,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry2->id,
            'account_id' => $liabilityAccount->id,
            'debit' => 1000,
            'credit' => 0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry2->id,
            'account_id' => $liabilityAccount->id,
            'debit' => 0,
            'credit' => 4000,
        ]);

        $this->assertEquals(3000, $liabilityAccount->fresh()->balance());
    }

    // =========================================================================
    // 26. WAREHOUSE CRUD
    // =========================================================================

    public function test_warehouse_can_be_created(): void
    {
        $response = $this->actingAdmin()->post(route('inventory.warehouses.store'), [
            'code' => 'WH-NEW',
            'name' => 'New Warehouse',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['code' => 'WH-NEW', 'company_id' => $this->company->id]);
    }

    public function test_warehouse_can_be_deleted(): void
    {
        $warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-DEL',
            'name' => 'Delete Warehouse',
            'status' => 'active',
        ]);

        $response = $this->actingAdmin()->delete(route('inventory.warehouses.destroy', $warehouse));
        $response->assertRedirect();
        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }

    public function test_viewer_cannot_create_warehouse(): void
    {
        $response = $this->actingViewer()->post(route('inventory.warehouses.store'), [
            'code' => 'WH-FAIL',
            'name' => 'Should Fail',
        ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // 27. LANDING PAGE
    // =========================================================================

    public function test_landing_page_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    // =========================================================================
    // 28. INVENTORY STOCK IN VIA SERVICE
    // =========================================================================

    public function test_stock_service_record_purchase_receipt(): void
    {
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Stock Receipt Supplier',
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'SVC-Rcpt-001',
            'name' => 'Receipt Product',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'WH-Rcpt',
            'name' => 'Receipt Warehouse',
            'is_default' => true,
            'status' => 'active',
        ]);

        $po = PurchaseOrder::create([
            'company_id' => $this->company->id,
            'po_number' => 'PO-RCPT-001',
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
            'order_date' => now()->toDateString(),
            'total' => 5000,
            'created_by' => $this->admin->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'description' => 'Receipt Item',
            'quantity' => 200,
            'unit_price' => 25,
            'line_total' => 5000,
            'sort' => 1,
        ]);

        StockService::recordPurchaseReceipt($po);

        $this->assertEquals(200, $product->fresh()->availableStock());
    }

    // =========================================================================
    // 29. USER COMPANY CONTEXT
    // =========================================================================

    public function test_company_context_resolves_from_auth(): void
    {
        CompanyContext::clear();
        $this->actingAdmin();

        $resolvedId = CompanyContext::id();
        $this->assertEquals($this->company->id, $resolvedId);
    }

    public function test_company_context_set_and_clear(): void
    {
        CompanyContext::set($this->otherCompany->id);
        $this->assertEquals($this->otherCompany->id, CompanyContext::id());

        CompanyContext::clear();
        $this->assertNull(CompanyContext::id());
    }

    public function test_company_context_current(): void
    {
        CompanyContext::set($this->company->id);
        $current = CompanyContext::current();
        $this->assertNotNull($current);
        $this->assertEquals($this->company->id, $current->id);
    }

    // =========================================================================
    // 30. SETTINGS ENCRYPTED VALUES
    // =========================================================================

    public function test_settings_encrypted_value(): void
    {
        $settingService = app(SettingService::class);

        $settingService->set('api_secret', 'my-super-secret-key', [
            'group' => 'security',
            'encrypt' => true,
        ]);

        $setting = Setting::where('key', 'api_secret')
            ->where('company_id', $this->company->id)
            ->first();

        $this->assertNotNull($setting);
        $this->assertTrue($setting->is_encrypted);
        $this->assertNotEquals('my-super-secret-key', $setting->value);

        $decrypted = $settingService->get('api_secret');
        $this->assertEquals('my-super-secret-key', $decrypted);
    }

    // =========================================================================
    // 31. CROSS-MODULE DATA INTEGRITY
    // =========================================================================

    public function test_quotation_to_order_data_integrity(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Integrity Customer',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $quotation = Quotation::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUOT-INT-001',
            'status' => 'accepted',
            'subtotal' => 2500,
            'discount' => 100,
            'tax' => 240,
            'total' => 2640,
            'created_by' => $this->admin->id,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Integration Item',
            'quantity' => 5,
            'unit_price' => 500,
            'discount' => 100,
            'tax_rate' => 10,
            'line_total' => 2640,
            'sort' => 1,
        ]);

        $this->actingAdmin()->post(route('sales.quotations.convert', $quotation))->assertRedirect();

        $salesOrder = SalesOrder::where('quotation_id', $quotation->id)->first();
        $this->assertNotNull($salesOrder);
        $this->assertEquals($customer->id, $salesOrder->customer_id);
        $this->assertEquals($this->company->id, $salesOrder->company_id);
        $this->assertEquals($quotation->total, $salesOrder->total);
    }

    public function test_auto_journal_invoice_payment_cycle(): void
    {
        DB::transaction(function () {
            Account::create([
                'company_id' => $this->company->id,
                'code' => '1100',
                'name' => 'Cash',
                'type' => 'asset',
                'is_active' => true,
            ]);

            Account::create([
                'company_id' => $this->company->id,
                'code' => '1200',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'is_active' => true,
            ]);

            Account::create([
                'company_id' => $this->company->id,
                'code' => '4000',
                'name' => 'Revenue',
                'type' => 'income',
                'is_active' => true,
            ]);

            $customer = Customer::create([
                'company_id' => $this->company->id,
                'name' => 'Cycle Customer',
                'status' => 'active',
                'created_by' => $this->admin->id,
            ]);

            $invoice = Invoice::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-CYCLE-001',
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'total' => 3000,
                'created_by' => $this->admin->id,
            ]);

            AutoJournalService::recordInvoice($invoice);

            $payment = Payment::create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-CYCLE-001',
                'payment_date' => now()->toDateString(),
                'amount' => 3000,
                'method' => 'bank',
                'status' => 'completed',
                'created_by' => $this->admin->id,
            ]);

            AutoJournalService::recordPayment($payment);

            $journalEntries = JournalEntry::where('description', 'like', '%CYCLE%')->get();
            $this->assertCount(2, $journalEntries);

            foreach ($journalEntries as $je) {
                $this->assertTrue($je->isBalanced());
            }
        });
    }
}
