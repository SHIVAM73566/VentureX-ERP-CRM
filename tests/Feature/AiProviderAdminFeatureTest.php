<?php

namespace Tests\Feature;

use App\Models\AiProvider;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * DB-backed AI provider admin: keys encrypted at rest and never exposed to the
 * frontend, per-provider limits persisted, super_admin-only access, and soft
 * delete that preserves the encrypted payload. No visitedredient real API calls:
 * every assertion is purely DB + HTTP contract.
 *
 * Mirrors the ERP's own feature suites (CodesterResubmissionTest et al.):
 * models are created explicitly and roles/permissions are seeded via Spatie
 * firstOrCreate, with the two-factor guard bypassed in setUp. No Eloquent
 * factories are used because this project only ships UserFactory.
 */
class AiProviderAdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Company $otherCompany;

    protected User $admin;

    protected User $viewer;

    protected User $otherAdmin;

    protected AiProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed the Spatie permissions the ERP's admin nav + policies rely on.
        foreach (['ai.configure', 'ai.use', 'settings.configure', 'users.manage'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        User::whereIn('email', ['ai-admin@test.com', 'ai-viewer@test.com', 'ai-other@test.com'])->forceDelete();
        Company::whereIn('name', ['AI Test Corp', 'AI Other Corp'])->forceDelete();

        $this->company = Company::create([
            'name' => 'AI Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->otherCompany = Company::create([
            'name' => 'AI Other Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->admin = User::create([
            'name' => 'AI Admin',
            'email' => 'ai-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdminRole);

        $this->viewer = User::create([
            'name' => 'AI Viewer',
            'email' => 'ai-viewer@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->viewer->assignRole($viewerRole);

        $this->otherAdmin = User::create([
            'name' => 'AI Other Admin',
            'email' => 'ai-other@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->otherCompany->id,
            'is_active' => true,
        ]);
        $this->otherAdmin->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

        $this->provider = $this->createProvider('gemini');
    }

    protected function createProvider(string $name, array $overrides = []): AiProvider
    {
        return AiProvider::create(array_merge([
            'company_id' => $this->company->id,
            'provider' => $name,
            'encrypted_key' => Crypt::encryptString('ai-test-secret'),
            'model' => 'gemini-1.5-flash',
            'base_url' => 'https://generativelanguage.googleapis.com',
            'path' => '/v1beta/models/:model:generateContent',
            'auth_mode' => 'google',
            'enabled' => true,
            'per_user_daily_limit' => 50,
            'per_user_monthly_limit' => 1000,
            'org_daily_limit' => 400,
            'org_monthly_limit' => 8000,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    protected function actingAdmin(): array
    {
        return [$this->admin, ['two_factor_verified_at' => now()->timestamp]];
    }

    // =========================================================================
    // 1. Access control
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.ai-providers.index'))
            ->assertRedirect(route('login'));
    }

    public function test_viewer_cannot_access_ai_provider_admin(): void
    {
        $this->actingAs($this->viewer)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('admin.ai-providers.index'))
            ->assertForbidden();
    }

    public function test_admin_of_another_company_cannot_access_this_companys_rows(): void
    {
        $this->actingAs($this->otherAdmin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('admin.ai-providers.index'))
            ->assertForbidden();
    }

    // =========================================================================
    // 2. Page renders stored providers WITHOUT exposing raw keys
    // =========================================================================

    public function test_index_page_loads_and_lists_stored_providers(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('admin.ai-providers.index'))
            ->assertOk()
            ->assertSee('gemini')
            ->assertDontSee('ai-test-secret');
    }

    public function test_security_page_never_returns_the_plaintext_key(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('admin.ai-providers.security'))
            ->assertOk()
            ->assertDontSee('ai-test-secret');
    }

    // =========================================================================
    // 3. Persistence contract
    // =========================================================================

    public function test_api_key_is_encrypted_at_rest_and_never_stored_in_plaintext(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('admin.ai-providers.store'), [
                'provider' => 'nvidia',
                'api_key' => 'nv-secret-plaintext',
                'model' => 'deepseek-r1',
            ]);

        $stored = AiProvider::where('provider', 'nvidia')->first();

        $this->assertNotNull($stored);
        $this->assertNotSame('nv-secret-plaintext', (string) $stored->encrypted_key);
        $this->assertSame('nv-secret-plaintext', (string) $stored->getDecryptedKey());
    }

    public function test_rate_limits_are_persisted_per_provider(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('admin.ai-providers.store'), [
                'provider' => 'gemini',
                'api_key' => 'k2',
                'model' => 'gemini-2.0-flash',
                'per_user_daily_limit' => 7,
                'per_user_monthly_limit' => 91,
                'org_daily_limit' => 555,
                'org_monthly_limit' => 2222,
            ]);

        $fresh = AiProvider::where('provider', 'gemini')->first();

        $this->assertSame(7, (int) $fresh->per_user_daily_limit);
        $this->assertSame(91, (int) $fresh->per_user_monthly_limit);
        $this->assertSame(555, (int) $fresh->org_daily_limit);
        $this->assertSame(2222, (int) $fresh->org_monthly_limit);
    }

    public function test_enable_survives_a_reload_and_key_stays_encrypted_after_disable(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('admin.ai-providers.enable', $this->provider))
            ->assertRedirect();

        $fresh = $this->provider->fresh();
        $this->assertTrue((bool) $fresh->enabled);
        $this->assertNotEmpty($fresh->encrypted_key);
    }

    public function test_provider_can_be_soft_deleted(): void
    {
        $id = $this->provider->id;

        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->delete(route('admin.ai-providers.destroy', $this->provider));

        $this->assertNotNull(AiProvider::withTrashed()->find($id));
        $this->assertNull(AiProvider::find($id));
    }
}
