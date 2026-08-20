<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Ai\AiDecisionEngine;
use App\Services\Ai\AiRouter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGatewayFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        if (! User::where('email', 'admin@jainmetal.example')->exists()) {
            $this->markTestSkipped('Demo data not seeded. Run: php artisan db:seed');
        }
    }

    protected function admin(): User
    {
        $user = User::where('email', 'admin@jainmetal.example')->firstOrFail();

        return $user;
    }

    protected function enableNvidia(): void
    {
        config(['ai.providers.nvidia.api_key' => 'test-key']);
        app(AiRouter::class)->healthReset('nvidia');
    }

    protected function fakeNvidiaOk(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => "Analysis for human review.\n[FACT] retrieved from the system.\n[RECOMMENDATION] confirm before acting."]]],
                'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 240],
            ], 200),
        ]);
    }

    public function test_landing_page_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('VentureX ERP & CRM', false);
        $response->assertSee('SoftwareApplication', false);
        $response->assertSee('Frequently asked questions', false);
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_dashboard_and_ai_pages_render(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp]);

        foreach ([
            '/', '/dashboard', '/ai/copilot', '/ai/usage', '/ai/assistant',
            '/ai/document-reader', '/ai/procurement', '/admin/settings?tab=ai',
        ] as $url) {
            $response = $this->get($url);

            if ($url === '/') {
                $response->assertRedirect('/dashboard');
            } else {
                $response->assertOk();
                $response->assertSee('VentureX ERP & CRM', false);
            }
        }
    }

    public function test_entity_show_pages_render_with_ai_actions(): void
    {
        $customer = Customer::ofCompany()->first();
        $lead = Lead::ofCompany()->first();
        $supplier = Supplier::ofCompany()->first();
        $invoice = Invoice::ofCompany()->first();
        $product = Product::ofCompany()->first();

        $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp]);

        $pages = [
            $customer ? '/customers/'.$customer->id : null,
            $lead ? '/leads/'.$lead->id : null,
            $supplier ? '/suppliers/'.$supplier->id : null,
            $invoice ? '/sales/invoices/'.$invoice->id : null,
            $product ? '/inventory/products/'.$product->id : null,
        ];

        foreach (array_filter($pages) as $url) {
            $this->get($url)->assertOk()->assertSee('x-data="aiAction()"', false);
        }
    }

    public function test_copilot_answers_local_questions_without_api_key(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp]);

        $response = $this->postJson('/ai/copilot', ['question' => 'Show me the overdue invoices']);

        $response->assertOk();
        $response->assertJsonPath('mode', 'local');
        $response->assertSeeText('Overdue', false);
    }

    public function test_copilot_returns_friendly_error_when_ai_unconfigured(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp]);

        config(['ai.providers.nvidia.api_key' => null]);

        $response = $this->postJson('/ai/copilot', ['question' => 'Give me a deep strategic analysis of our sales pipeline momentum and competitive positioning.']);

        $response->assertStatus(502);
        $response->assertJsonMissing(['content']);
    }

    public function test_ai_action_requires_ai_permission(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp]);

        $this->postJson('/ai/actions/finance', ['question' => 'Which customers owe us money?'])->assertOk();
    }

    public function test_ai_insights_generate_returns_friendly_error_when_unconfigured(): void
    {
        $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp]);

        config(['ai.providers.nvidia.api_key' => null]);

        $this->postJson('/ai/insights/generate')->assertStatus(502);
    }

    public function test_unauthenticated_ai_endpoints_redirect(): void
    {
        $this->postJson('/ai/copilot', ['question' => 'test'])->assertUnauthorized();
        $this->postJson('/ai/insights/generate')->assertUnauthorized();
    }

    public function test_decision_engine_gate_only_escalates_complex_high_impact_tasks(): void
    {
        $engine = app(AiDecisionEngine::class);

        foreach (['customer_summary', 'email_draft', 'lead_email', 'inventory_analysis', 'finance_analysis', 'business_summary'] as $task) {
            $this->assertFalse($engine->shouldUseClaude($task), $task.' must not use Claude.');
            $this->assertLessThan(70, $engine->impact($task));
        }

        foreach (['deep_supplier_analysis', 'negotiation_strategy', 'deep_customer_analysis', 'opportunity_analysis', 'supplier_comparison', 'executive_review', 'daily_priorities', 'complex_document_analysis', 'complex_business_question', 'strategic_recommendation'] as $task) {
            $this->assertGreaterThanOrEqual(70, $engine->impact($task));
        }

        $this->assertSame(5, $engine->priorityLevel('executive_review'));
        $this->assertLessThan(5, $engine->priorityLevel('customer_summary'));
    }

    public function test_simple_tasks_never_route_to_claude(): void
    {
        $router = app(AiRouter::class);

        foreach (['customer_summary', 'email_draft', 'lead_email', 'inventory_analysis', 'finance_analysis'] as $task) {
            $result = $router->resolve($task);
            $this->assertNotSame('claude', $result['provider']);
        }

        $this->assertSame('nvidia', $router->resolve('deep_supplier_analysis')['provider']);
        $this->assertSame('nvidia', $router->resolve('executive_review')['provider']);
    }

    public function test_deep_supplier_analysis_routes_to_nvidia(): void
    {
        $this->enableNvidia();
        $this->fakeNvidiaOk();

        $supplier = Supplier::ofCompany()->first();
        $this->assertNotNull($supplier);

        $response = $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson('/ai/actions/deep/supplier', ['supplier_id' => $supplier->id]);

        $response->assertOk();
        $response->assertJsonPath('mode', 'ai');
        $response->assertJsonPath('provider', 'nvidia');
        $response->assertJsonPath('cached', false);
        $this->assertStringContainsString('[FACT]', (string) $response->json('content'));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'integrate.api.nvidia.com'));
    }

    public function test_deep_analysis_is_cached_then_reused_without_new_api_call(): void
    {
        $this->enableNvidia();
        $this->fakeNvidiaOk();

        $supplier = Supplier::ofCompany()->first();
        $this->actingAs($this->admin())->withSession(['two_factor_verified_at' => now()->timestamp]);

        $this->postJson('/ai/actions/deep/supplier', ['supplier_id' => $supplier->id])->assertJsonPath('cached', false);

        $cached = $this->postJson('/ai/actions/deep/supplier', ['supplier_id' => $supplier->id]);
        $cached->assertOk();
        $cached->assertJsonPath('cached', true);

        Http::assertSentCount(1);
    }

    public function test_deep_analysis_returns_local_facts_when_all_providers_fail(): void
    {
        $this->enableNvidia();

        Http::fake(['*' => Http::response(['error' => 'boom'], 500)]);

        $supplier = Supplier::ofCompany()->first();
        $this->assertNotNull($supplier);

        $response = $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson('/ai/actions/deep/supplier', ['supplier_id' => $supplier->id]);

        $response->assertOk();
        $response->assertJsonPath('mode', 'local');
        $this->assertStringContainsString('AI analysis is currently unavailable', (string) $response->json('content'));
        $this->assertStringContainsString($supplier->name, (string) $response->json('content'));
    }

    public function test_negotiation_and_opportunity_analyses_route_to_nvidia(): void
    {
        $this->enableNvidia();
        $this->fakeNvidiaOk();

        $this->actingAs($this->admin())->withSession(['two_factor_verified_at' => now()->timestamp]);

        $supplier = Supplier::ofCompany()->first();
        if ($supplier) {
            $this->postJson('/ai/actions/deep/negotiation', ['supplier_id' => $supplier->id])
                ->assertOk()
                ->assertJsonPath('provider', 'nvidia');
        }

        $opportunity = Opportunity::ofCompany()->first();
        if ($opportunity) {
            $this->postJson('/ai/actions/deep/opportunity', ['opportunity_id' => $opportunity->id])
                ->assertOk()
                ->assertJsonPath('provider', 'nvidia');
        }

        $customer = Customer::ofCompany()->first();
        if ($customer) {
            $this->postJson('/ai/actions/deep/customer', ['customer_id' => $customer->id])
                ->assertOk()
                ->assertJsonPath('provider', 'nvidia');
        }
    }

    public function test_executive_review_and_daily_priorities_route_to_nvidia(): void
    {
        $this->enableNvidia();
        $this->fakeNvidiaOk();

        $this->actingAs($this->admin())->withSession(['two_factor_verified_at' => now()->timestamp]);

        $this->postJson('/ai/executive-review')
            ->assertOk()
            ->assertJsonPath('provider', 'nvidia');

        $this->postJson('/ai/daily-priorities')
            ->assertOk()
            ->assertJsonPath('provider', 'nvidia');
    }

    public function test_strategic_copilot_question_routes_to_nvidia(): void
    {
        $this->enableNvidia();
        $this->fakeNvidiaOk();

        $response = $this->actingAs($this->admin())
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson('/ai/copilot', ['question' => 'Give me a strategic recommendation for our business']);

        $response->assertOk();
        $response->assertJsonPath('provider', 'nvidia');
    }

    public function test_provider_health_tracks_failures(): void
    {
        $this->enableNvidia();

        Http::fake(['*' => Http::response(['error' => 'boom'], 500)]);

        $supplier = Supplier::ofCompany()->first();
        $this->actingAs($this->admin())->withSession(['two_factor_verified_at' => now()->timestamp]);

        $this->postJson('/ai/actions/deep/supplier', ['supplier_id' => $supplier->id])->assertOk();

        $this->assertGreaterThanOrEqual(1, (int) Cache::get('ai:health:nvidia', 0));
    }

    public function test_complex_tasks_are_config_routed_to_nvidia(): void
    {
        foreach (['deep_supplier_analysis', 'negotiation_strategy', 'executive_review', 'daily_priorities', 'complex_document_analysis', 'strategic_recommendation'] as $task) {
            $routing = config("ai.task_routing.{$task}");
            $this->assertIsArray($routing);
            $this->assertContains('nvidia', $routing['providers'] ?? []);
        }
    }
}
