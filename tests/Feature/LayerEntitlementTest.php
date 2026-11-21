<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\LayerEntitlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LayerEntitlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Temporary test route
        Route::middleware(['web', 'auth', 'layer:updates_support'])->get('/__test-layer', function () {
            return response('ok');
        });
    }

    public function test_config_has_fifteen_layers(): void
    {
        $layers = config('layers.layers');
        $this->assertIsArray($layers);
        $this->assertCount(15, $layers);
    }

    public function test_middleware_blocks_without_entitlement(): void
    {
        $company = Company::create(['name' => 'Test Co', 'email' => 't@t.com', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user)
            ->get('/__test-layer')
            ->assertStatus(403);
    }

    public function test_middleware_allows_with_active_entitlement(): void
    {
        $company = Company::create(['name' => 'Test Co', 'email' => 't2@t.com', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id]);

        LayerEntitlement::create([
            'company_id' => $company->id,
            'layer_key' => 'updates_support',
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        $this->actingAs($user)
            ->get('/__test-layer')
            ->assertStatus(200)
            ->assertSee('ok');
    }
}
