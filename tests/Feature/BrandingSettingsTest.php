<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\BrandingService;
use App\Services\CompanyContext;
use App\Services\SettingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Company branding: settings tab, persistence, logo lifecycle and dynamic
 * invoice/quotation PDF rendering with no hard-coded brand values.
 */
class BrandingSettingsTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->company = Company::create([
            'name' => 'Fallback Company Ltd',
            'legal_name' => 'Fallback Company Limited',
            'email' => 'billing@fallback.test',
            'phone' => '+1 555 0100',
            'tax_id' => 'TAX-FALLBACK-1',
            'address_line1' => '1 Fallback Street',
            'city' => 'Springfield',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        CompanyContext::set($this->company->id);

        Permission::firstOrCreate(['name' => 'settings.configure', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'invoices.view', 'guard_name' => 'web']);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'Branding Admin',
            'email' => 'branding-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdmin);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();

        parent::tearDown();
    }

    protected function actingAdmin()
    {
        return $this->actingAs($this->admin)
            ->withSession([
                'two_factor_verified_at' => now()->timestamp,
                'step_up_verified_at' => now()->timestamp,
            ]);
    }

    protected function brandingPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Acme Billing Co',
            'legal_name' => 'Acme Billing Company Private Limited',
            'tagline' => 'Invoices that close deals',
            'email' => 'accounts@acme.test',
            'phone' => '+91 90000 00000',
            'website' => 'https://acme.test',
            'address' => '42 Ledger Lane, Mumbai',
            'tax_id' => 'GSTIN-27AAAAA0000A1Z5',
            'registration_number' => 'CIN-U12345MH2026PTC000001',
            'accent_color' => '#0f766e',
            'font_family' => 'DejaVu Sans, sans-serif',
            'invoice_footer' => 'Acme Billing Co · Thank you for your business.',
            'invoice_terms' => 'Payment due within 15 days. Quote the invoice number.',
            'watermark_text' => 'PAID COPY',
            'show_watermark' => '1',
        ], $overrides);
    }

    public function test_branding_tab_renders(): void
    {
        $response = $this->actingAdmin()->get(route('admin.settings.index', ['tab' => 'branding']));

        $response->assertOk();
        $response->assertSee('Invoice branding', false);
        $response->assertSee('Visual identity', false);
        $response->assertSee('Watermark text', false);
    }

    public function test_branding_update_persists_settings(): void
    {
        $response = $this->actingAdmin()->post(route('admin.settings.branding'), $this->brandingPayload());

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'branding']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'company_id' => $this->company->id,
            'key' => 'branding.company_name',
            'value' => 'Acme Billing Co',
        ]);
        $this->assertDatabaseHas('settings', [
            'company_id' => $this->company->id,
            'key' => 'branding.tax_id',
            'value' => 'GSTIN-27AAAAA0000A1Z5',
        ]);
        $this->assertDatabaseHas('settings', [
            'company_id' => $this->company->id,
            'key' => 'branding.show_watermark',
            'value' => '1',
        ]);

        $branding = app(BrandingService::class)->all();

        $this->assertSame('Acme Billing Co', $branding['company_name']);
        $this->assertSame('#0f766e', $branding['accent_color']);
        $this->assertSame('DejaVu Sans, sans-serif', $branding['font_family']);
        $this->assertTrue($branding['show_watermark']);
    }

    public function test_branding_falls_back_to_company_record(): void
    {
        $branding = app(BrandingService::class)->all();

        $this->assertSame('Fallback Company Ltd', $branding['company_name']);
        $this->assertSame('TAX-FALLBACK-1', $branding['tax_id']);
        $this->assertStringContainsString('1 Fallback Street', $branding['address']);
        $this->assertSame('#1a365d', $branding['accent_color']);
    }

    public function test_invalid_accent_colour_is_rejected(): void
    {
        $this->actingAdmin()
            ->from(route('admin.settings.index', ['tab' => 'branding']))
            ->post(route('admin.settings.branding'), $this->brandingPayload(['accent_color' => 'not-a-colour']))
            ->assertSessionHasErrors('accent_color');
    }

    public function test_logo_can_be_uploaded_and_removed(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 240, 80);

        $this->actingAdmin()
            ->post(route('admin.settings.branding'), $this->brandingPayload(['logo' => $file]))
            ->assertSessionHas('success');

        $path = app(SettingService::class)->get('branding.logo_path');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $branding = app(BrandingService::class)->all();
        $this->assertNotNull($branding['logo_url']);
        $this->assertStringStartsWith('data:image/', (string) $branding['logo_data_uri']);

        $this->actingAdmin()
            ->post(route('admin.settings.branding'), $this->brandingPayload(['remove_logo' => '1']))
            ->assertSessionHas('success');

        $this->assertEmpty(app(SettingService::class)->get('branding.logo_path'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invoice_pdf_view_uses_saved_branding(): void
    {
        $this->actingAdmin()
            ->post(route('admin.settings.branding'), $this->brandingPayload())
            ->assertSessionHas('success');

        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Invoice Customer',
            'email' => 'customer@test.com',
            'status' => 'active',
            'currency_code' => 'USD',
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'invoice_number' => 'INV-BRAND-001',
            'customer_id' => $customer->id,
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 250,
            'total' => 250,
            'paid_amount' => 0,
            'created_by' => $this->admin->id,
        ]);

        $invoice->load('customer', 'items', 'payments');

        $html = view('sales.invoices.pdf', [
            'invoice' => $invoice,
            'branding' => app(BrandingService::class)->all(),
        ])->render();

        $this->assertStringContainsString('Acme Billing Co', $html);
        $this->assertStringContainsString('GSTIN-27AAAAA0000A1Z5', $html);
        $this->assertStringContainsString('#0f766e', $html);
        $this->assertStringContainsString('PAID COPY', $html);
        $this->assertStringContainsString('DejaVu Sans, sans-serif', $html);
        $this->assertStringContainsString('Payment due within 15 days', $html);

        // The live PDF endpoint still returns a valid document.
        $this->actingAdmin()->get(route('sales.invoices.pdf', $invoice))->assertOk();
    }

    public function test_invoice_show_page_surfaces_branding_preview(): void
    {
        $this->actingAdmin()
            ->post(route('admin.settings.branding'), $this->brandingPayload());

        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Preview Customer',
            'email' => 'preview@test.com',
            'status' => 'active',
            'currency_code' => 'USD',
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'invoice_number' => 'INV-BRAND-002',
            'customer_id' => $customer->id,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'subtotal' => 100,
            'total' => 100,
            'paid_amount' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAdmin()->get(route('sales.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Acme Billing Co', false)
            ->assertSee('Invoice PDF uses your saved branding.', false);
    }
}
