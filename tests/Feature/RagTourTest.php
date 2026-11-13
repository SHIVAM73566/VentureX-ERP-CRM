<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\User;
use App\Services\Rag\RagPipelineRegistry;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RagTourTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        $this->company = Company::create([
            'name' => 'RAG Tour Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'Tour Admin',
            'email' => 'tour-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        $this->admin->assignRole($role);
    }

    public function test_guests_are_redirected_from_the_tour(): void
    {
        $this->get(route('ai.rag.tour'))->assertRedirect(route('login'));
    }

    public function test_tour_renders_all_six_domains_and_their_pipelines(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ai.rag.tour'));

        $response->assertOk();
        $response->assertSee('80 RAG pipelines, one business brain', false);

        foreach (RagPipelineRegistry::domainLabels() as $label) {
            // Blade escapes "&" in the label, so assert against the escaped form.
            $response->assertSee($label);
        }

        // One representative pipeline from every domain.
        $response->assertSee('Customer Lifetime Value', false);
        $response->assertSee('Supplier Reliability Scoring', false);
        $response->assertSee('Stockout Risk Alerting', false);
        $response->assertSee('Receivables Aging Insight', false);
        $response->assertSee('Container Demurrage Risk', false);
        $response->assertSee('Company Health Scorecard', false);
    }

    public function test_tour_explains_live_usage_benefits_and_governance(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ai.rag.tour'));

        $response->assertOk();
        $response->assertSee('What happens when someone asks a question', false);
        $response->assertSee('A live enterprise day with RAG', false);
        $response->assertSee('How every kind of company benefits', false);
        $response->assertSee('Trust, security and explainability', false);
        $response->assertSee('Company-scoped', false);
    }

    public function test_tour_route_is_not_shadowed_by_the_pipeline_json_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ai.rag.tour'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');
    }

    public function test_rag_workspace_links_to_the_tour(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ai.rag'));

        $response->assertOk();
        $response->assertSee('Open the pipeline tour', false);
    }
}
