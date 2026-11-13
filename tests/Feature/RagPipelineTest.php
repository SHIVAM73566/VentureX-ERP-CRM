<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\Customer;
use App\Models\RagPipeline;
use App\Models\User;
use App\Services\Rag\EmbeddingService;
use App\Services\Rag\RagPipelineRegistry;
use App\Services\Rag\VectorStoreService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RagPipelineTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        $this->company = Company::create([
            'name' => 'RAG Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'RAG Admin',
            'email' => 'rag-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        $this->admin->assignRole($role);
    }

    public function test_registry_defines_exactly_eighty_pipelines(): void
    {
        $this->assertSame(80, RagPipelineRegistry::count());

        $this->assertSame([
            'crm' => 15,
            'procurement' => 15,
            'inventory' => 10,
            'finance' => 15,
            'logistics' => 15,
            'executive' => 10,
        ], RagPipelineRegistry::domainCounts());
    }

    public function test_vector_store_registers_eighty_unique_pipelines(): void
    {
        $count = app(VectorStoreService::class)->ensurePipelines();

        $this->assertSame(80, $count);
        $this->assertSame(80, RagPipeline::query()->count());
        $this->assertSame(80, RagPipeline::query()->distinct()->count('key'));

        // Re-running must not duplicate rows.
        app(VectorStoreService::class)->ensurePipelines();
        $this->assertSame(80, RagPipeline::query()->count());
    }

    public function test_embeddings_rank_related_text_higher(): void
    {
        $embeddings = app(EmbeddingService::class);

        $query = $embeddings->embed('customer churn retention risk');
        $related = $embeddings->embed('customer retention churn risk analysis');
        $unrelated = $embeddings->embed('container freight customs duty');

        $this->assertGreaterThan(
            $embeddings->similarity($query, $unrelated),
            $embeddings->similarity($query, $related)
        );
    }

    public function test_dashboard_renders_for_super_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('ai.rag'));

        $response->assertOk();
        $response->assertSee('Enterprise RAG');
    }

    public function test_query_endpoint_returns_local_answer_with_sources(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Acme Retention Corp',
            'email' => 'ops@acme.test',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson(route('ai.rag.query'), [
                'pipeline' => 'customer_lifetime_value',
                'question' => 'customer lifetime value retention revenue',
                'limit' => 5,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('pipeline.key', 'customer_lifetime_value');

        $this->assertNotEmpty($response->json('sources'));
        $this->assertSame('local', $response->json('mode'));
    }

    public function test_unknown_pipeline_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson(route('ai.rag.query'), [
                'pipeline' => 'does_not_exist',
                'question' => 'anything',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_sync_endpoint_indexes_pipeline_documents(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Indexed Customer',
            'email' => 'indexed@example.test',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->postJson(route('ai.rag.sync'), ['pipeline' => 'customer_360_profile']);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertGreaterThanOrEqual(1, $response->json('indexed'));
    }

    public function test_api_lists_all_pipelines(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/ai/rag/pipelines');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertSame(80, $response->json('data.count'));
    }
}
