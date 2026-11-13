<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\User;
use App\Services\Rag\RagPageGuide;
use App\Services\Rag\RagPipelineRegistry;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductGuideTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        $this->company = Company::create([
            'name' => 'Product Guide Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        // The dashboard renders permission-aware widgets, so the test needs
        // the real permission catalogue rather than a bare role.
        $this->seed(PermissionSeeder::class);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::all());

        $this->admin = User::create([
            'name' => 'Guide Admin',
            'email' => 'guide-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        $this->admin->assignRole($role);
    }

    /**
     * The guide is rendered by the authenticated layout, so every page in the
     * application must expose it without a dedicated controller or route.
     *
     * @return array<int, array<int, string>>
     */
    public static function pageProvider(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'getting started' => ['getting-started'],
            'customers' => ['customers.index'],
            'leads' => ['leads.index'],
            'quotations' => ['sales.quotations.index'],
            'purchase orders' => ['procurement.orders.index'],
            'products' => ['inventory.products.index'],
            'shipments' => ['logistics.shipments.index'],
            'finance' => ['finance.dashboard'],
            'support' => ['support.index'],
            'users' => ['admin.users.index'],
            'audit logs' => ['admin.audit-logs.index'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_guide_is_available_on_every_major_page(string $route): void
    {
        $response = $this->actingAs($this->admin)->get(route($route));

        $response->assertOk();
        $response->assertSee('Product guide', false);
        $response->assertSee('All 80 RAG pipelines', false);
        $response->assertSee('This page', false);
    }

    public function test_guide_lists_all_eighty_pipelines_on_a_single_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();

        $definitions = RagPipelineRegistry::definitions();
        $this->assertCount(80, $definitions);

        foreach ($definitions as $pipeline) {
            $response->assertSee($pipeline['name']);
        }

        foreach (RagPipelineRegistry::domainLabels() as $label) {
            $response->assertSee($label);
        }

        $response->assertSee('80 company-scoped pipelines', false);
    }

    public function test_guide_introduction_explains_the_product_and_process(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('One login. Every department. 80 AI pipelines.', false);
        $response->assertSee('Start in 60 seconds', false);
        $response->assertSee('How companies use VentureX', false);
        $response->assertSee('The easy process', false);
        $response->assertSee('Features that work together', false);
        $response->assertSee('This guide follows you everywhere', false);
    }

    public function test_guide_is_page_aware_on_finance_pages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.dashboard'));

        $response->assertOk();
        $response->assertSee('Finance, Invoicing &amp; Ledger Auditing', false);
        $response->assertSee('Which invoices are 30+ days overdue and who owns the follow-up?', false);
        $response->assertSee('Ask in the RAG workspace', false);
    }

    public function test_guide_is_page_aware_on_crm_pages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('CRM &amp; Sales Analytics', false);
        $response->assertSee('Which 10 customers are most likely to churn this quarter?', false);
        $response->assertSee('Customers', false);
    }

    public function test_page_guide_service_maps_routes_and_paths_to_domains(): void
    {
        $this->assertSame(['crm'], RagPageGuide::domainsFor('customers.index', 'customers'));
        $this->assertSame(['procurement'], RagPageGuide::domainsFor('procurement.orders.index', 'procurement/orders'));
        $this->assertSame(['inventory'], RagPageGuide::domainsFor('inventory.stock.index', 'inventory/stock'));
        $this->assertSame(['finance', 'crm'], RagPageGuide::domainsFor('sales.invoices.index', 'sales/invoices'));
        $this->assertSame(['logistics'], RagPageGuide::domainsFor('logistics.shipments.index', 'logistics/shipments'));
        $this->assertSame(['executive', 'finance'], RagPageGuide::domainsFor('admin.audit-logs.index', 'admin/audit-logs'));

        // Path fallback when the route has no name.
        $this->assertSame(['finance'], RagPageGuide::domainsFor(null, 'finance/receivables'));

        // Unknown screens still receive the full catalogue.
        $this->assertCount(6, RagPageGuide::domainsFor(null, 'unknown/screen'));
    }

    public function test_page_guide_service_builds_labels_and_questions(): void
    {
        $this->assertSame('Receivables', RagPageGuide::pageLabel('finance.receivables', 'finance/receivables'));
        $this->assertSame('Procurement', RagPageGuide::pageLabel(null, 'procurement/orders'));

        $questions = RagPageGuide::suggestionsFor(['finance'], 6);
        $this->assertNotEmpty($questions);
        $this->assertContains('Project our cash position for the next 8 weeks.', $questions);

        $context = RagPageGuide::context('finance.receivables', 'finance/receivables');
        $this->assertSame('Receivables', $context['page']);
        $this->assertArrayHasKey('finance', $context['domain_labels']);
        $this->assertNotEmpty($context['suggestions']);
    }
}
