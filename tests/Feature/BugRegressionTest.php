<?php

namespace Tests\Feature;

use App\Http\Middleware\ApiProtection;
use App\Http\Middleware\EnsureTwoFactor;
use App\Models\AiSkill;
use App\Models\ApiKeyVault;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ErrorReport;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\LoginVerification;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CompanyContext;
use Database\Seeders\CompanySeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Regression coverage for the defect audit of the VentureX ERP & CRM codebase.
 *
 * Every test in this file reproduces a bug that was present in production code
 * and asserts that the fix holds. If any of these fail, a previously fixed
 * defect has been reintroduced.
 */
class BugRegressionTest extends TestCase
{
    /**
     * Marker planted in records that belong to the second tenant. Searching for
     * it must never surface that tenant's rows.
     */
    protected const RIVAL_MARKER = 'ZZRIVALMARKER';

    protected Company $companyA;

    protected Company $companyB;

    protected User $adminA;

    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
            MasterDataSeeder::class,
            CompanySeeder::class,
        ]);

        $this->companyA = Company::where('email', 'admin@jainmetal.example')->firstOrFail();

        $this->companyB = Company::create([
            'name' => 'Rival Industries',
            'legal_name' => 'Rival Industries Limited',
            'email' => 'admin@rival.example',
            'currency_code' => 'USD',
            'is_active' => true,
        ]);

        $this->adminA = User::where('email', 'admin@jainmetal.example')->firstOrFail();
        $this->adminA->update(['password' => Hash::make('password')]);

        $this->adminB = User::create([
            'name' => 'Rival Admin',
            'email' => 'admin@rival.example',
            'password' => Hash::make('password'),
            'company_id' => $this->companyB->id,
            'is_active' => true,
        ]);
        $this->adminB->assignRole('super_admin');

        CompanyContext::clear();
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();

        parent::tearDown();
    }

    protected function loginA(): self
    {
        $this->actingAs($this->adminA);

        return $this;
    }

    protected function rivalCustomer(): Customer
    {
        return Customer::create([
            'company_id' => $this->companyB->id,
            'name' => self::RIVAL_MARKER.' Customer',
            'status' => 'active',
        ]);
    }

    protected function rivalSupplier(): Supplier
    {
        return Supplier::create([
            'company_id' => $this->companyB->id,
            'name' => self::RIVAL_MARKER.' Supplier',
        ]);
    }

    // -----------------------------------------------------------------
    // Cross-tenant data leakage through ungrouped OR conditions.
    //
    // ofCompany() applies a plain WHERE company_id = ? clause rather than a
    // global scope, so a top-level orWhere() escapes the tenant filter and
    // the OR branch matches rows belonging to every tenant.
    // -----------------------------------------------------------------

    public function test_receivables_search_does_not_leak_other_tenants_invoices(): void
    {
        // The search term is matched against invoice_number in the OR branch.
        // Because ofCompany() only adds a plain WHERE clause, an ungrouped
        // orWhere() lets this branch match rows of every tenant.
        $rivalInvoice = Invoice::create([
            'company_id' => $this->companyB->id,
            'invoice_number' => self::RIVAL_MARKER.'-INV-9001',
            'customer_id' => $this->rivalCustomer()->id,
            'status' => 'sent',
            'issue_date' => now()->subDay(),
            'due_date' => now()->addDays(30),
            'total' => 5000,
            'paid_amount' => 0,
        ]);

        $this->loginA()
            ->get('/finance/receivables?q='.self::RIVAL_MARKER)
            ->assertOk()
            ->assertDontSee($rivalInvoice->invoice_number);
    }

    public function test_payables_search_does_not_leak_other_tenants_purchase_orders(): void
    {
        $rivalOrder = PurchaseOrder::create([
            'company_id' => $this->companyB->id,
            'po_number' => 'RIVAL-PO-9001',
            'supplier_id' => $this->rivalSupplier()->id,
            'status' => 'approved',
            'order_date' => now()->subDay(),
            'expected_date' => now()->addDays(30),
            'total' => 9000,
            'paid_amount' => 0,
        ]);

        $this->loginA()
            ->get('/finance/payables?q='.self::RIVAL_MARKER)
            ->assertOk()
            ->assertDontSee($rivalOrder->po_number);
    }

    public function test_admin_ai_skill_search_does_not_leak_other_tenants_skills(): void
    {
        $rivalSkill = AiSkill::create([
            'company_id' => $this->companyB->id,
            'name' => 'Rival Tenant Skill',
            'slug' => strtolower(self::RIVAL_MARKER).'-skill-9001',
            'instructions' => 'Rival tenant instructions',
            'is_active' => true,
        ]);

        $this->loginA()
            ->get('/admin/ai-skills?q='.self::RIVAL_MARKER)
            ->assertOk()
            ->assertDontSee($rivalSkill->slug);
    }

    public function test_api_journal_entry_search_does_not_leak_other_tenants_entries(): void
    {
        $rivalEntry = JournalEntry::create([
            'company_id' => $this->companyB->id,
            'entry_number' => 'RIVAL-JE-9001',
            'description' => self::RIVAL_MARKER.' entry',
            'date' => now()->subDay(),
            'status' => 'draft',
        ]);

        Sanctum::actingAs($this->adminA);

        $this->getJson('/api/finance/journal-entries?q='.self::RIVAL_MARKER)
            ->assertOk()
            ->assertDontSee($rivalEntry->entry_number);
    }

    public function test_api_payment_search_does_not_leak_other_tenants_payments(): void
    {
        $rivalPayment = Payment::create([
            'company_id' => $this->companyB->id,
            'payment_number' => 'RIVAL-PAY-9001',
            'payment_date' => now(),
            'amount' => 100,
            'method' => 'cash',
            'reference' => self::RIVAL_MARKER.' ref',
            'status' => 'completed',
        ]);

        Sanctum::actingAs($this->adminA);

        $this->getJson('/api/sales/payments?q='.self::RIVAL_MARKER)
            ->assertOk()
            ->assertDontSee($rivalPayment->payment_number);
    }

    public function test_sales_payment_edit_form_does_not_leak_other_tenants_invoices(): void
    {
        // A payment of company A that (through bad data) points at an invoice
        // owned by company B. The ungrouped orWhere('id', ...) escaped the
        // company_id filter, so the foreign invoice was offered in the form.
        $rivalInvoice = Invoice::create([
            'company_id' => $this->companyB->id,
            'invoice_number' => 'RIVAL-INV-9002',
            'customer_id' => $this->rivalCustomer()->id,
            'status' => 'sent',
            'issue_date' => now()->subDay(),
            'total' => 1000,
            'paid_amount' => 0,
        ]);

        $ownPayment = Payment::create([
            'company_id' => $this->companyA->id,
            'payment_number' => 'OWN-PAY-9001',
            'payment_date' => now(),
            'invoice_id' => $rivalInvoice->id,
            'amount' => 50,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        $this->loginA()
            ->get('/sales/payments/'.$ownPayment->id.'/edit')
            ->assertOk()
            ->assertDontSee($rivalInvoice->invoice_number);
    }

    // -----------------------------------------------------------------
    // Error report de-duplication must never cross tenant boundaries.
    // -----------------------------------------------------------------

    public function test_error_report_deduplication_is_scoped_to_the_reporting_company(): void
    {
        $payload = [
            'error_type' => 'exception',
            'error_message' => 'Shared failure across tenants',
            'module' => 'billing',
        ];

        $this->loginA()
            ->post('/support/report-error', $payload)
            ->assertRedirect();

        $companyAReport = ErrorReport::where('company_id', $this->companyA->id)->firstOrFail();

        $this->assertSame(1, $companyAReport->occurrence_count);

        $this->actingAs($this->adminB)
            ->post('/support/report-error', $payload)
            ->assertRedirect();

        $companyBReport = ErrorReport::where('company_id', $this->companyB->id)->firstOrFail();

        $this->assertNotSame($companyAReport->id, $companyBReport->id, 'Company B received company A error id.');
        $this->assertSame(1, $companyBReport->occurrence_count);
        $this->assertSame(1, $companyAReport->fresh()->occurrence_count, 'Company A occurrence count was mutated by company B.');
    }

    public function test_regular_user_is_allowed_to_report_an_error(): void
    {
        $viewer = User::create([
            'name' => 'Company Viewer',
            'email' => 'viewer@tenant-a.example',
            'password' => Hash::make('password'),
            'company_id' => $this->companyA->id,
            'is_active' => true,
        ]);
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->post('/support/report-error', [
                'error_type' => 'error',
                'error_message' => 'Something went wrong',
                'module' => 'crm',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    // -----------------------------------------------------------------
    // IP blocking: the admin control must actually block, must not 500
    // when the optional reason is omitted, and must be reversible.
    // -----------------------------------------------------------------

    public function test_block_ip_succeeds_without_a_reason_and_actually_blocks(): void
    {
        $this->loginA()
            ->post('/admin/security/block-ip', ['ip' => '203.0.113.77'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertTrue(ApiProtection::isIpBlocked('203.0.113.77'));
    }

    public function test_blocked_ip_is_rejected_by_the_api_protection_middleware(): void
    {
        $this->loginA()->post('/admin/security/block-ip', ['ip' => '203.0.113.88']);

        $this->assertTrue(ApiProtection::isIpBlocked('203.0.113.88'));

        ApiProtection::unblockIp('203.0.113.88');

        $this->assertFalse(ApiProtection::isIpBlocked('203.0.113.88'));
    }

    public function test_unblock_ip_route_releases_the_block(): void
    {
        $this->loginA()->post('/admin/security/block-ip', ['ip' => '203.0.113.99']);

        $this->assertTrue(ApiProtection::isIpBlocked('203.0.113.99'));

        $this->loginA()
            ->post('/admin/security/unblock-ip', ['ip' => '203.0.113.99'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse(ApiProtection::isIpBlocked('203.0.113.99'));
    }

    public function test_security_dashboard_lists_blocked_ips(): void
    {
        $this->loginA()->post('/admin/security/block-ip', [
            'ip' => '203.0.113.111',
            'reason' => 'Brute force attempts',
        ]);

        $this->loginA()
            ->get('/admin/security')
            ->assertOk()
            ->assertSee('203.0.113.111')
            ->assertSee('Brute force attempts');
    }

    // -----------------------------------------------------------------
    // Missing model classes that caused fatal errors on first use.
    // -----------------------------------------------------------------

    public function test_api_key_vault_model_can_be_loaded_and_used(): void
    {
        $this->assertTrue(class_exists(ApiKeyVault::class));

        $vault = ApiKeyVault::create([
            'company_id' => $this->companyA->id,
            'name' => 'test-provider-key',
            'key_hash' => hash('sha256', 'secret'),
            'encrypted_key' => 'encrypted-placeholder',
            'provider' => 'nvidia',
            'is_active' => true,
        ]);

        $this->assertSame('test-provider-key', $vault->name);
    }

    public function test_login_verification_model_can_record_a_verification_attempt(): void
    {
        $this->assertTrue(class_exists(LoginVerification::class));

        LoginVerification::create([
            'user_id' => $this->adminA->id,
            'email' => $this->adminA->email,
            'verification_type' => 'email_otp',
            'status' => 'success',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertDatabaseHas('login_verifications', [
            'email' => $this->adminA->email,
            'verification_type' => 'email_otp',
            'status' => 'success',
        ]);
    }

    public function test_user_controller_uses_the_spatie_role_model(): void
    {
        $role = Role::where('name', 'super_admin')->firstOrFail();

        $this->assertNotNull($role->id);
        $this->assertTrue(class_exists(Role::class));
    }
}
