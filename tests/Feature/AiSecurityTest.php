<?php

namespace Tests\Feature;

use App\Http\Controllers\Ai\AiAssistantController;
use App\Http\Controllers\Ai\AiInsightsController;
use App\Http\Controllers\Ai\CopilotController;
use App\Http\Controllers\Ai\DeepAnalysisController;
use App\Http\Controllers\Ai\ExecutiveController;
use App\Http\Middleware\EnsureTwoFactor;
use App\Models\AiRun;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Ai\AiLocalIntelligence;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiUsageMonitor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSecurityTest extends TestCase
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
        return User::where('email', 'admin@jainmetal.example')->firstOrFail();
    }

    protected function authAdmin(): self
    {
        $this->actingAs($this->admin());
        $this->withSession(['two_factor_verified_at' => now()->timestamp]);

        return $this;
    }

    // =========================================================================
    // 1. AI ENDPOINTS REQUIRE AUTHENTICATION
    // =========================================================================

    public function test_copilot_requires_authentication(): void
    {
        $this->postJson('/ai/copilot', ['question' => 'test'])->assertUnauthorized();
        $this->get('/ai/copilot')->assertRedirect();
    }

    public function test_assistant_requires_authentication(): void
    {
        $this->get('/ai/assistant')->assertRedirect();
        $this->postJson('/ai/assistant', ['message' => 'test'])->assertUnauthorized();
    }

    public function test_insights_generate_requires_authentication(): void
    {
        $this->postJson('/ai/insights/generate')->assertUnauthorized();
    }

    public function test_usage_dashboard_requires_authentication(): void
    {
        $this->get('/ai/usage')->assertRedirect();
    }

    public function test_quota_endpoint_requires_authentication(): void
    {
        $this->getJson('/ai/quota')->assertUnauthorized();
    }

    public function test_action_endpoints_require_authentication(): void
    {
        $this->postJson('/ai/actions/customer-summary', ['customer_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/lead-email', ['lead_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/supplier-analysis', ['supplier_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/invoice-summary', ['invoice_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/inventory', ['question' => 'test'])->assertUnauthorized();
        $this->postJson('/ai/actions/finance', ['question' => 'test'])->assertUnauthorized();
    }

    public function test_deep_analysis_endpoints_require_authentication(): void
    {
        $this->postJson('/ai/actions/deep/supplier', ['supplier_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/deep/negotiation', ['supplier_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/deep/customer', ['customer_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/deep/opportunity', ['opportunity_id' => 1])->assertUnauthorized();
        $this->postJson('/ai/actions/deep/procurement', [])->assertUnauthorized();
    }

    public function test_executive_endpoints_require_authentication(): void
    {
        $this->postJson('/ai/executive-review')->assertUnauthorized();
        $this->postJson('/ai/daily-priorities')->assertUnauthorized();
    }

    public function test_document_reader_requires_authentication(): void
    {
        $this->get('/ai/document-reader')->assertRedirect();
        $this->postJson('/ai/document-reader', ['document_id' => 1])->assertUnauthorized();
    }

    public function test_procurement_ai_requires_authentication(): void
    {
        $this->get('/ai/procurement')->assertRedirect();
        $this->postJson('/ai/procurement', ['question' => 'test'])->assertUnauthorized();
    }

    // =========================================================================
    // 2. AI ENDPOINTS REQUIRE PROPER PERMISSIONS
    // =========================================================================

    public function test_unauthorized_user_gets_redirect_or_forbidden(): void
    {
        $viewer = User::where('email', '!=', 'admin@jainmetal.example')->first();
        if (! $viewer) {
            $this->markTestSkipped('No secondary user available.');
        }

        $response = $this->actingAs($viewer)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson('/ai/copilot', ['question' => 'test']);

        // Should be 403 Forbidden or 302 Redirect (both indicate proper auth enforcement)
        $this->assertContains($response->getStatusCode(), [302, 403]);
    }

    // =========================================================================
    // 3. AI ENDPOINTS HANDLE MISSING API KEYS GRACEFULLY
    // =========================================================================

    public function test_copilot_handles_missing_api_key_gracefully(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Give me a strategic analysis of our business'])
            ->assertStatus(502);
    }

    public function test_assistant_handles_missing_api_key_gracefully(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin()
            ->postJson('/ai/assistant', ['message' => 'Strategic analysis of our business'])
            ->assertStatus(502);
    }

    public function test_local_questions_work_without_any_api_key(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Show me the overdue invoices'])
            ->assertOk()
            ->assertJsonPath('mode', 'local');
    }

    public function test_local_inventory_questions_work_without_api_key(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin()
            ->postJson('/ai/actions/inventory', ['question' => 'Which products are below reorder level?'])
            ->assertOk()
            ->assertJsonPath('mode', 'local');
    }

    public function test_local_finance_questions_work_without_api_key(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin()
            ->postJson('/ai/actions/finance', ['question' => 'Show me the overdue invoices'])
            ->assertOk()
            ->assertJsonPath('mode', 'local');
    }

    // =========================================================================
    // 4. AI ENDPOINTS HANDLE API FAILURES GRACEFULLY
    // =========================================================================

    public function test_copilot_returns_friendly_error_on_api_failure(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);

        Http::fake(['*' => Http::response(['error' => 'Internal Server Error'], 500)]);

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Strategic analysis of our pipeline and business'])
            ->assertStatus(502)
            ->assertJsonFragment(['error' => 'AI copilot is temporarily unavailable. Please try again later.']);
    }

    public function test_assistant_returns_friendly_error_on_api_failure(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);

        Http::fake(['*' => Http::response(['error' => 'Internal Server Error'], 500)]);

        $this->authAdmin()
            ->postJson('/ai/assistant', ['message' => 'Strategic analysis of our entire business'])
            ->assertStatus(502)
            ->assertJsonFragment(['error' => 'AI assistant is temporarily unavailable. Please try again later.']);
    }

    public function test_action_endpoint_returns_friendly_error_on_api_failure(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);

        Http::fake(['*' => Http::response(['error' => 'timeout'], 504)]);

        $this->authAdmin()
            ->postJson('/ai/insights/generate')
            ->assertStatus(502)
            ->assertJsonFragment(['error' => 'Unable to generate insights at this time.']);
    }

    public function test_deep_analysis_returns_local_facts_on_api_failure(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);
        config(['ai.providers.gemini.api_key' => 'fake-key']);
        config(['ai.providers.deepseek.api_key' => 'fake-key']);
        config(['ai.providers.claude.api_key' => 'fake-key']);

        Http::fake(['*' => Http::response(['error' => 'boom'], 500)]);

        $supplier = Supplier::ofCompany()->first();
        if (! $supplier) {
            $this->markTestSkipped('No suppliers available for deep analysis test.');
        }

        $this->authAdmin()
            ->postJson('/ai/actions/deep/supplier', ['supplier_id' => $supplier->id])
            ->assertOk()
            ->assertJsonPath('mode', 'local')
            ->assertSeeText('AI analysis is currently unavailable');
    }

    public function test_provider_timeout_returns_friendly_error(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);

        Http::fake(fn () => throw new ConnectionException('cURL error 28'));

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Strategic analysis of our pipeline'])
            ->assertStatus(502)
            ->assertJsonMissing(['trace']);
    }

    // =========================================================================
    // 5. AI ENDPOINTS DON'T EXPOSE API KEYS IN RESPONSES
    // =========================================================================

    public function test_ai_response_never_exposes_api_keys(): void
    {
        config(['ai.providers.swift.api_key' => 'sk-test-super-secret-key-12345']);
        config(['ai.providers.claude.api_key' => 'claude-secret-key-67890']);

        $this->authAdmin();

        $response = $this->postJson('/ai/copilot', ['question' => 'Show me the overdue invoices']);
        $response->assertOk();
        $responseContent = $response->getContent();
        $this->assertStringNotContainsString('sk-test-super-secret-key-12345', $responseContent);
        $this->assertStringNotContainsString('claude-secret-key-67890', $responseContent);
        $this->assertStringNotContainsString('api_key', $responseContent);
    }

    public function test_quota_endpoint_never_exposes_api_keys(): void
    {
        $this->authAdmin();
        $response = $this->getJson('/ai/quota');
        $response->assertOk();
        $responseContent = $response->getContent();
        $this->assertStringNotContainsString('api_key', $responseContent);
        $this->assertStringNotContainsString('RAPIDAPI_KEY', $responseContent);
    }

    public function test_usage_dashboard_never_exposes_api_keys(): void
    {
        $this->authAdmin();
        $response = $this->get('/ai/usage');
        $response->assertOk();
        $responseContent = $response->getContent();
        $this->assertStringNotContainsString('api_key', $responseContent);
        $this->assertStringNotContainsString('RAPIDAPI_KEY', $responseContent);
    }

    public function test_ai_error_messages_dont_leak_internal_details(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin();
        $response = $this->postJson('/ai/copilot', ['question' => 'Complex strategic analysis requiring advanced AI']);

        $content = $response->getContent();
        $this->assertStringNotContainsString('api_key', $content);
        $this->assertStringNotContainsString('RAPIDAPI_KEY', $content);
        $this->assertStringNotContainsString('rapidapi.com', $content);
    }

    public function test_failed_ai_run_records_dont_expose_keys(): void
    {
        config(['ai.providers.swift.api_key' => 'secret-key']);

        Http::fake(['*' => Http::response(['error' => 'fail'], 500)]);

        $this->authAdmin()
            ->postJson('/ai/actions/customer-summary', ['customer_id' => 1]);

        $run = AiRun::where('status', 'failed')->latest()->first();
        if ($run) {
            $runJson = json_encode($run->toArray());
            $this->assertStringNotContainsString('secret-key', $runJson);
        }
    }

    // =========================================================================
    // 6. AI ENDPOINTS HAVE PROPER RATE LIMITING
    // =========================================================================

    public function test_copilot_has_rate_limiting_middleware(): void
    {
        $route = app('router')->getRoutes()->getByAction(CopilotController::class.'@ask');
        $this->assertNotNull($route);
        $middleware = collect($route->gatherMiddleware());
        $this->assertTrue(
            $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
            'Copilot ask endpoint must have throttle middleware.'
        );
    }

    public function test_assistant_send_has_rate_limiting_middleware(): void
    {
        $route = app('router')->getRoutes()->getByAction(AiAssistantController::class.'@send');
        $this->assertNotNull($route);
        $middleware = collect($route->gatherMiddleware());
        $this->assertTrue(
            $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
            'Assistant send endpoint must have throttle middleware.'
        );
    }

    public function test_action_endpoints_have_rate_limiting(): void
    {
        $routeNames = [
            'ai.actions.customer-summary',
            'ai.actions.supplier-analysis',
        ];

        foreach ($routeNames as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            if ($route) {
                $middleware = collect($route->gatherMiddleware());
                $this->assertTrue(
                    $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
                    "Route {$name} must have throttle middleware."
                );
            }
        }
    }

    public function test_deep_analysis_endpoints_have_rate_limiting(): void
    {
        foreach ([
            DeepAnalysisController::class.'@supplier',
            DeepAnalysisController::class.'@negotiation',
            DeepAnalysisController::class.'@customer',
        ] as $action) {
            $route = app('router')->getRoutes()->getByAction($action);
            $this->assertNotNull($route, "Route for {$action} must exist.");
            $middleware = collect($route->gatherMiddleware());
            $this->assertTrue(
                $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
                "{$action} must have throttle middleware."
            );
        }
    }

    public function test_executive_endpoints_have_rate_limiting(): void
    {
        foreach ([
            ExecutiveController::class.'@review',
            ExecutiveController::class.'@daily',
        ] as $action) {
            $route = app('router')->getRoutes()->getByAction($action);
            $this->assertNotNull($route, "Route for {$action} must exist.");
            $middleware = collect($route->gatherMiddleware());
            $this->assertTrue(
                $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
                "{$action} must have throttle middleware."
            );
        }
    }

    public function test_insights_generate_has_rate_limiting(): void
    {
        $route = app('router')->getRoutes()->getByAction(AiInsightsController::class.'@generate');
        $this->assertNotNull($route);
        $middleware = collect($route->gatherMiddleware());
        $this->assertTrue(
            $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
            'Insights generate must have throttle middleware.'
        );
    }

    // =========================================================================
    // 7. AI USAGE IS PROPERLY TRACKED
    // =========================================================================

    public function test_local_copilot_answer_creates_ai_run_record(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);

        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => 'Analysis result'], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
        ], 200)]);

        $beforeCount = AiRun::count();

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Strategic analysis of our entire business and pipeline'])
            ->assertOk();

        $this->assertGreaterThan($beforeCount, AiRun::count());
    }

    public function test_ai_run_records_company_and_user(): void
    {
        config(['ai.providers.swift.api_key' => 'fake-key']);

        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => 'Analysis result'], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
        ], 200)]);

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Strategic analysis of our entire business and pipeline'])
            ->assertOk();

        $run = AiRun::latest()->first();
        $this->assertNotNull($run);
        $this->assertEquals($this->admin()->id, $run->user_id);
        $this->assertNotNull($run->company_id);
    }

    public function test_quota_service_tracks_usage(): void
    {
        $quota = app(AiQuotaService::class);
        $user = $this->admin();
        $beforeUsed = $quota->dailyUsed($user->id);

        $quota->record($user->id);

        $afterUsed = $quota->dailyUsed($user->id);
        $this->assertGreaterThan($beforeUsed, $afterUsed);
    }

    public function test_usage_monitor_tracks_requests(): void
    {
        $monitor = app(AiUsageMonitor::class);
        $statsBefore = $monitor->stats(1);

        $monitor->request('test_task');

        $statsAfter = $monitor->stats(1);
        $this->assertGreaterThan($statsBefore['requests'], $statsAfter['requests']);
    }

    public function test_usage_monitor_tracks_errors(): void
    {
        $monitor = app(AiUsageMonitor::class);
        $statsBefore = $monitor->stats(1);

        $monitor->error('test_provider');

        $statsAfter = $monitor->stats(1);
        $this->assertGreaterThan($statsBefore['errors'], $statsAfter['errors']);
    }

    public function test_usage_monitor_tracks_cache_hits(): void
    {
        $monitor = app(AiUsageMonitor::class);
        $statsBefore = $monitor->stats(1);

        $monitor->cacheHit('test_task');

        $statsAfter = $monitor->stats(1);
        $this->assertGreaterThan($statsBefore['cache_hits'], $statsAfter['cache_hits']);
    }

    public function test_usage_monitor_tracks_costs(): void
    {
        $monitor = app(AiUsageMonitor::class);
        $statsBefore = $monitor->stats(1);

        $monitor->addCost(0.05);

        $statsAfter = $monitor->stats(1);
        $this->assertGreaterThan($statsBefore['estimated_cost'], $statsAfter['estimated_cost']);
    }

    public function test_usage_monitor_tracks_latency(): void
    {
        $monitor = app(AiUsageMonitor::class);
        $statsBefore = $monitor->stats(1);

        $monitor->addLatency(1500);

        $statsAfter = $monitor->stats(1);
        $this->assertGreaterThan(0, $statsAfter['avg_latency_ms']);
    }

    // =========================================================================
    // 8. AI FALLBACK WORKS WHEN PROVIDERS ARE DOWN
    // =========================================================================

    public function test_copilot_local_fallback_returns_value_when_ai_unavailable(): void
    {
        config(['ai.providers.swift.api_key' => '']);
        config(['ai.providers.gemini.api_key' => '']);
        config(['ai.providers.deepseek.api_key' => '']);
        config(['ai.providers.claude.api_key' => '']);
        config(['ai.providers.nvidia.api_key' => '']);

        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Show me the overdue invoices'])
            ->assertOk()
            ->assertJsonPath('mode', 'local');
    }

    public function test_local_intelligence_handles_overdue_invoices(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('Show me the overdue invoices');
        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertTrue(strlen($result) > 0);
    }

    public function test_local_intelligence_handles_payables(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('Show me the payables');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_local_intelligence_handles_leads_due(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('Which leads need follow up today?');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_local_intelligence_returns_null_for_unknown_question(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('What is the meaning of life?');
        $this->assertNull($result);
    }

    public function test_local_intelligence_handles_top_opportunities(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('Show me the top opportunities in the pipeline');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_local_intelligence_handles_open_purchase_orders(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('Show me open purchase orders that need attention');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_local_intelligence_handles_customers_owing(): void
    {
        $local = app(AiLocalIntelligence::class);
        $result = $local->answer('Which customers owe us money?');
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    // =========================================================================
    // BONUS: QUOTA ENFORCEMENT
    // =========================================================================

    public function test_quota_service_blocks_when_exhausted(): void
    {
        $quota = app(AiQuotaService::class);
        $user = $this->admin();

        $this->actingAs($user);

        Cache::put(
            'ai:quota:day:'.$user->id.':'.now()->format('Y-m-d'),
            9999,
            172800
        );

        $result = $quota->check();
        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['daily_remaining']);

        Cache::forget('ai:quota:day:'.$user->id.':'.now()->format('Y-m-d'));
    }

    public function test_quota_service_allows_within_limit(): void
    {
        $quota = app(AiQuotaService::class);
        $user = $this->admin();

        $this->actingAs($user);

        Cache::forget('ai:quota:day:'.$user->id.':'.now()->format('Y-m-d'));
        Cache::forget('ai:quota:week:'.$user->id.':'.now()->format('Y-W'));

        $result = $quota->check();
        $this->assertTrue($result['allowed']);
        $this->assertGreaterThan(0, $result['daily_remaining']);
    }

    // =========================================================================
    // BONUS: VALIDATION
    // =========================================================================

    public function test_copilot_rejects_empty_question(): void
    {
        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => ''])
            ->assertUnprocessable();
    }

    public function test_copilot_rejects_missing_question(): void
    {
        $this->authAdmin()
            ->postJson('/ai/copilot', [])
            ->assertUnprocessable();
    }

    public function test_assistant_rejects_empty_message(): void
    {
        $this->authAdmin()
            ->postJson('/ai/assistant', ['message' => ''])
            ->assertUnprocessable();
    }

    public function test_action_endpoints_validate_required_fields(): void
    {
        $this->authAdmin()
            ->postJson('/ai/actions/customer-summary', [])
            ->assertUnprocessable();

        $this->authAdmin()
            ->postJson('/ai/actions/lead-email', [])
            ->assertUnprocessable();

        $this->authAdmin()
            ->postJson('/ai/actions/supplier-analysis', [])
            ->assertUnprocessable();

        $this->authAdmin()
            ->postJson('/ai/actions/invoice-summary', [])
            ->assertUnprocessable();
    }

    public function test_inventory_action_validates_question_max_length(): void
    {
        $this->authAdmin()
            ->postJson('/ai/actions/inventory', ['question' => str_repeat('a', 2001)])
            ->assertUnprocessable();
    }

    public function test_copilot_validates_question_max_length(): void
    {
        $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => str_repeat('a', 2001)])
            ->assertUnprocessable();
    }
}
