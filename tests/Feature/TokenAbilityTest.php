<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TokenAbilityTest extends TestCase
{
    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Token Ability Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->user = User::create([
            'name' => 'Token User',
            'email' => 'token-ability@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
    }

    public function test_read_only_token_can_read_but_not_mutate(): void
    {
        $token = $this->user->createToken('read-only', ['customers.read']);

        $this->withToken($token->plainTextToken)
            ->getJson('/api/crm/customers')
            ->assertOk();

        $this->withToken($token->plainTextToken)
            ->postJson('/api/crm/customers', ['name' => 'Should Not Exist'])
            ->assertForbidden();

        $customer = Customer::create(['company_id' => $this->company->id, 'name' => 'Existing Customer']);

        $this->withToken($token->plainTextToken)
            ->deleteJson('/api/crm/customers/'.$customer->id)
            ->assertForbidden();

        $this->assertDatabaseMissing('customers', ['name' => 'Should Not Exist']);
    }

    public function test_write_token_cannot_be_used_for_unrelated_modules(): void
    {
        $token = $this->user->createToken('customers-writer', ['customers.write']);

        $this->withToken($token->plainTextToken)
            ->postJson('/api/crm/customers', ['name' => 'Allowed Customer'])
            ->assertCreated();

        $this->withToken($token->plainTextToken)
            ->getJson('/api/inventory/products')
            ->assertForbidden();

        $this->withToken($token->plainTextToken)
            ->postJson('/api/inventory/products', ['name' => 'Not Allowed'])
            ->assertForbidden();
    }

    public function test_ai_and_rag_routes_require_ai_abilities(): void
    {
        $token = $this->user->createToken('customers-only', ['customers.read']);

        $this->withToken($token->plainTextToken)
            ->getJson('/api/ai/insights')
            ->assertForbidden();

        $this->withToken($token->plainTextToken)
            ->postJson('/api/ai/rag/query', ['pipeline' => 'x', 'question' => 'y'])
            ->assertForbidden();

        // Sanctum caches the resolved guard between requests in one test
        // process; forget it when switching tokens.
        $this->app['auth']->forgetGuards();

        $aiToken = $this->user->createToken('ai-reader', ['ai.read']);

        $this->withToken($aiToken->plainTextToken)
            ->getJson('/api/ai/insights')
            ->assertOk();

        $this->app['auth']->forgetGuards();

        // A RAG query is POST but is semantically a read operation.
        $this->withToken($aiToken->plainTextToken)
            ->postJson('/api/ai/rag/query', ['pipeline' => 'missing_pipeline', 'question' => 'y'])
            ->assertStatus(422);
    }

    public function test_limited_token_cannot_manage_tokens(): void
    {
        $token = $this->user->createToken('read-only', ['customers.read']);

        // Token self-management is a privilege escalation surface: a limited
        // token must never list, create or revoke tokens.
        $this->withToken($token->plainTextToken)
            ->getJson('/api/tokens')
            ->assertForbidden();

        $this->withToken($token->plainTextToken)
            ->postJson('/api/tokens', ['name' => 'escalated', 'abilities' => ['ai.write']])
            ->assertForbidden();

        $this->withToken($token->plainTextToken)
            ->deleteJson('/api/tokens/1')
            ->assertForbidden();
    }

    public function test_rag_scoped_ability_is_accepted_on_rag_routes(): void
    {
        $token = $this->user->createToken('rag-reader', ['rag.read']);

        $this->withToken($token->plainTextToken)
            ->getJson('/api/ai/rag/pipelines')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_session_and_first_party_requests_are_not_blocked_by_abilities(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/crm/customers', ['name' => 'Session Customer'])
            ->assertCreated();

        $this->app['auth']->forgetGuards();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/crm/customers')
            ->assertOk();
    }
}
