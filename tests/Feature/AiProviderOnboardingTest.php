<?php

namespace Tests\Feature;

use App\Models\AiProvider;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Services\Ai\Adapters\OpenAiCompatibleAdapter;
use App\Services\Ai\AiCredentialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Zero-config, key-only AI provider onboarding (Admin -> Settings -> AI Engine).
 *
 * The contract under test:
 * - one key input per provider; no base URL / path / auth fields accepted
 * - keys are encrypted at rest, fingerprinted, masked and never flashed back
 * - prefix validation keeps a vendor key out of the wrong provider
 * - every write requires step-up auth and is audit logged with the tail only
 * - hardcoded endpoints/models come from config, not the browser
 */
class AiProviderOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Company $otherCompany;

    protected User $admin;

    protected User $viewer;

    /** @var array<string, array{key: string, model: string, base_url: string, path: string, auth_mode: string}> */
    protected array $providers = [
        'nvidia' => [
            'key' => 'nvapi-1234567890abcdefghijklmnopqrstuvwxyz',
            'model' => 'nvidia/llama-3.3-nemotron-super-49b-v1.5',
            'base_url' => 'https://integrate.api.nvidia.com',
            'path' => '/v1/chat/completions',
            'auth_mode' => 'bearer',
        ],
        'gemini' => [
            'key' => 'AIzaSyA1234567890abcdefghijklmnopqrstu',
            'model' => 'gemini-2.5-flash',
            'base_url' => 'https://generativelanguage.googleapis.com',
            'path' => '/v1beta/models/{model}:generateContent',
            'auth_mode' => 'google',
        ],
        'openai' => [
            'key' => 'sk-proj-1234567890abcdefghijklmnopqrstuvwxyz',
            'model' => 'gpt-4o',
            'base_url' => 'https://api.openai.com/v1/chat/completions',
            'path' => '/v1/chat/completions',
            'auth_mode' => 'bearer',
        ],
        'anthropic' => [
            'key' => 'sk-ant-api03-1234567890abcdefghijklmnopqrstuvwxyz',
            'model' => 'claude-sonnet-4-20250514',
            'base_url' => 'https://api.anthropic.com',
            'path' => '/v1/messages',
            'auth_mode' => 'anthropic',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['ai.configure', 'ai.use', 'settings.configure'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $this->company = Company::create([
            'name' => 'Onboarding Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->otherCompany = Company::create([
            'name' => 'Onboarding Other Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $this->admin = User::create([
            'name' => 'Onboarding Admin',
            'email' => 'onboarding-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdminRole);

        $this->viewer = User::create([
            'name' => 'Onboarding Viewer',
            'email' => 'onboarding-viewer@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->viewer->assignRole($viewerRole);
    }

    protected function stepUpSession(): array
    {
        return [
            'two_factor_verified_at' => now()->timestamp,
            'step_up_verified_at' => now()->timestamp,
        ];
    }

    protected function storeKey(string $provider, array $overrides = [])
    {
        return $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.store', $provider), array_merge([
                'api_key' => $this->providers[$provider]['key'],
            ], $overrides));
    }

    // =========================================================================
    // Access control + step-up
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.settings.index', ['tab' => 'ai']))
            ->assertRedirect(route('login'));
    }

    public function test_non_super_admin_cannot_view_or_write_keys(): void
    {
        $this->actingAs($this->viewer)
            ->withSession($this->stepUpSession())
            ->get(route('admin.settings.index', ['tab' => 'ai']))
            ->assertForbidden();

        foreach (array_keys($this->providers) as $provider) {
            $this->actingAs($this->viewer)
                ->withSession($this->stepUpSession())
                ->post(route('admin.settings.ai-key.store', $provider), [
                    'api_key' => $this->providers[$provider]['key'],
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('ai_providers', 0);
    }

    public function test_saving_a_key_requires_step_up_authentication(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('admin.settings.ai-key.store', 'nvidia'), [
                'api_key' => $this->providers['nvidia']['key'],
            ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('step-up', (string) $response->headers->get('Location'));
        $this->assertDatabaseCount('ai_providers', 0);
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.store', 'evil-provider'), [
                'api_key' => 'nvapi-1234567890abcdefghijklmnopqrstuvwxyz',
            ])
            ->assertNotFound();
    }

    // =========================================================================
    // Page rendering
    // =========================================================================

    public function test_settings_page_shows_one_card_per_provider_without_endpoint_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->get(route('admin.settings.index', ['tab' => 'ai']));

        $response->assertOk()
            ->assertSee('AERO X Super Intelligence Brain')
            ->assertSee('NVIDIA NIM')
            ->assertSee('Google Gemini')
            ->assertSee('OpenAI')
            ->assertSee('Anthropic Claude')
            ->assertSee('Not connected')
            ->assertDontSee('name="base_url"', false)
            ->assertDontSee('name="path"', false);
    }

    public function test_settings_page_masks_stored_keys_and_is_not_cacheable(): void
    {
        $this->storeKey('nvidia');

        $response = $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->get(route('admin.settings.index', ['tab' => 'ai']));

        $response->assertOk()
            ->assertSee(substr($this->providers['nvidia']['key'], -4))
            ->assertDontSee($this->providers['nvidia']['key']);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $response->headers->get('Cache-Control'));
    }

    // =========================================================================
    // Key-only onboarding for every provider
    // =========================================================================

    public function test_each_provider_can_be_connected_with_only_a_key(): void
    {
        foreach ($this->providers as $provider => $expected) {
            $this->storeKey($provider)
                ->assertRedirect(route('admin.settings.index', ['tab' => 'ai']))
                ->assertSessionHas('success');

            $row = AiProvider::query()
                ->where('company_id', $this->company->id)
                ->where('provider', $provider)
                ->first();

            $this->assertNotNull($row, $provider.' row was not created');
            $this->assertSame($expected['key'], $row->getDecryptedKey(), $provider.' key did not round-trip');
            $this->assertNotSame($expected['key'], $row->encrypted_key, $provider.' key is not encrypted');
            $this->assertNotEmpty($row->key_hash, $provider.' fingerprint missing');
            $this->assertSame($expected['model'], $row->model, $provider.' default model mismatch');
            $this->assertSame($expected['base_url'], $row->base_url, $provider.' endpoint must come from config');
            $this->assertSame($expected['path'], $row->path, $provider.' path must come from config');
            $this->assertSame($expected['auth_mode'], $row->auth_mode, $provider.' auth mode mismatch');
            $this->assertTrue($row->enabled, $provider.' must auto-enable on save');
        }
    }

    public function test_browser_supplied_endpoints_are_ignored(): void
    {
        $this->storeKey('nvidia', [
            'base_url' => 'https://attacker.example/steal',
            'path' => '/exfiltrate',
            'auth_mode' => 'rapidapi',
            'host' => 'attacker.example',
        ])->assertRedirect(route('admin.settings.index', ['tab' => 'ai']));

        $row = AiProvider::query()->where('provider', 'nvidia')->firstOrFail();

        $this->assertSame('https://integrate.api.nvidia.com', $row->base_url);
        $this->assertSame('/v1/chat/completions', $row->path);
        $this->assertSame('bearer', $row->auth_mode);
    }

    public function test_model_override_is_limited_to_the_allowed_catalogue(): void
    {
        $this->storeKey('openai', ['model' => 'gpt-4o-mini']);

        $this->assertSame('gpt-4o-mini', AiProvider::query()->where('provider', 'openai')->firstOrFail()->model);

        $this->storeKey('openai', ['model' => 'evil-model-from-browser']);

        $this->assertSame('gpt-4o', AiProvider::query()->where('provider', 'openai')->firstOrFail()->model);
    }

    // =========================================================================
    // Validation — no plaintext round-trip
    // =========================================================================

    public function test_each_provider_rejects_another_vendors_key(): void
    {
        $wrongKeys = [
            'nvidia' => 'sk-proj-1234567890abcdefghijklmnopqrstuvwxyz',
            'gemini' => 'nvapi-1234567890abcdefghijklmnopqrstuvwxyz',
            'openai' => 'sk-ant-api03-1234567890abcdefghijklmnopqrstuvwxyz',
            'anthropic' => 'sk-proj-1234567890abcdefghijklmnopqrstuvwxyz',
        ];

        foreach ($wrongKeys as $provider => $wrongKey) {
            $response = $this->actingAs($this->admin)
                ->withSession($this->stepUpSession())
                ->post(route('admin.settings.ai-key.store', $provider), ['api_key' => $wrongKey]);

            $response->assertSessionHasErrors('api_key');
        }

        $this->assertDatabaseCount('ai_providers', 0);
    }

    public function test_short_keys_are_rejected(): void
    {
        foreach (['nvidia' => 'nvapi-short', 'gemini' => 'AIza-short', 'openai' => 'sk-short', 'anthropic' => 'sk-ant-short'] as $provider => $key) {
            $this->actingAs($this->admin)
                ->withSession($this->stepUpSession())
                ->post(route('admin.settings.ai-key.store', $provider), ['api_key' => $key])
                ->assertSessionHasErrors('api_key');
        }

        $this->assertDatabaseCount('ai_providers', 0);
    }

    public function test_secret_is_never_flashed_back_on_validation_error(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.store', 'nvidia'), [
                'api_key' => 'sk-openai-not-an-nvidia-key-1234567890',
            ]);

        $response->assertSessionHasErrors('api_key');

        $oldInput = session('_old_input', []);
        $this->assertIsArray($oldInput);
        $this->assertArrayNotHasKey('api_key', $oldInput);
    }

    // =========================================================================
    // Audit + rotation + removal
    // =========================================================================

    public function test_response_and_audit_log_never_contain_the_plaintext_key(): void
    {
        $this->storeKey('anthropic');

        $key = $this->providers['anthropic']['key'];

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->get(route('admin.settings.index', ['tab' => 'ai']))
            ->assertOk()
            ->assertDontSee($key);

        $log = AuditLog::query()->where('event', 'ai_provider_saved')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertStringNotContainsString($key, (string) json_encode($log->new_value));
        $this->assertSame(substr($key, -4), $log->new_value['key_tail']);
        $this->assertSame('anthropic', $log->new_value['provider']);
    }

    public function test_replacing_a_key_rotates_the_ciphertext(): void
    {
        $this->storeKey('nvidia');

        $first = AiProvider::query()->where('provider', 'nvidia')->firstOrFail();
        $firstCiphertext = $first->encrypted_key;

        $replacement = 'nvapi-zyxwvutsrqponmlkjihgfedcba9876543210';
        $this->storeKey('nvidia', ['api_key' => $replacement]);

        $fresh = $first->fresh();

        $this->assertNotSame($firstCiphertext, $fresh->encrypted_key);
        $this->assertSame($replacement, $fresh->getDecryptedKey());
        $this->assertSame(1, AiProvider::query()->where('provider', 'nvidia')->count());
    }

    public function test_configuration_check_reports_success_without_echoing_the_key(): void
    {
        $this->storeKey('gemini');

        $response = $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.test', 'gemini'));

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'ai']))
            ->assertSessionHas('success');

        $message = (string) session('success');
        $this->assertStringContainsString('configuration check passed', $message);
        $this->assertStringNotContainsString($this->providers['gemini']['key'], $message);
        $this->assertStringContainsString(substr($this->providers['gemini']['key'], -4), $message);
    }

    public function test_configuration_check_without_a_key_returns_an_error(): void
    {
        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.test', 'openai'))
            ->assertRedirect(route('admin.settings.index', ['tab' => 'ai']))
            ->assertSessionHas('error');
    }

    public function test_key_can_be_removed_and_the_row_is_soft_deleted(): void
    {
        $this->storeKey('openai');

        $provider = AiProvider::query()->where('provider', 'openai')->firstOrFail();
        $id = $provider->id;

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->delete(route('admin.settings.ai-key.destroy', 'openai'))
            ->assertRedirect(route('admin.settings.index', ['tab' => 'ai']))
            ->assertSessionHas('success');

        $this->assertNull(AiProvider::find($id));
        $this->assertNotNull(AiProvider::withTrashed()->find($id));
    }

    public function test_removing_a_missing_key_returns_an_error(): void
    {
        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->delete(route('admin.settings.ai-key.destroy', 'anthropic'))
            ->assertRedirect(route('admin.settings.index', ['tab' => 'ai']))
            ->assertSessionHas('error');
    }

    // =========================================================================
    // Tenant isolation
    // =========================================================================

    public function test_keys_are_scoped_to_the_acting_company(): void
    {
        $this->storeKey('nvidia');

        $other = AiProvider::create([
            'company_id' => $this->otherCompany->id,
            'provider' => 'nvidia',
            'encrypted_key' => app(AiCredentialService::class)->encryptKey('nvapi-other-company-key-000000000000'),
            'model' => 'nvidia/llama-3.3-nemotron-super-49b-v1.5',
            'enabled' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->get(route('admin.settings.index', ['tab' => 'ai']))
            ->assertOk()
            ->assertDontSee('nvapi-other-company-key-000000000000');

        $own = AiProvider::query()
            ->where('provider', 'nvidia')
            ->where('id', '!=', $other->id)
            ->firstOrFail();

        $this->assertSame($this->company->id, $own->company_id);
    }

    // =========================================================================
    // Anthropic direct API contract (key-only path must actually work)
    // =========================================================================

    public function test_anthropic_adapter_uses_native_messages_contract(): void
    {
        config([
            'ai.providers.anthropic.api_key' => 'sk-ant-api03-1234567890abcdefghijklmnopqrstuvwxyz',
            'ai.providers.anthropic.api_version' => '2023-06-01',
        ]);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model' => 'claude-sonnet-4-20250514',
                'content' => [
                    ['type' => 'text', 'text' => 'Answer for human review.'],
                ],
                'usage' => ['input_tokens' => 42, 'output_tokens' => 84],
            ], 200),
        ]);

        $result = app(OpenAiCompatibleAdapter::class)->generate('anthropic', [
            'model' => 'claude-sonnet-4-20250514',
            'system' => 'You are a business analyst.',
            'user' => 'Summarise the pipeline.',
            'temperature' => 0.4,
            'max_tokens' => 1024,
        ]);

        $this->assertSame('Answer for human review.', $result['content']);
        $this->assertSame(42, $result['prompt_tokens']);
        $this->assertSame(84, $result['completion_tokens']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'sk-ant-api03-1234567890abcdefghijklmnopqrstuvwxyz')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && ! $request->hasHeader('Authorization')
                && $request['system'] === 'You are a business analyst.'
                && $request['messages'] === [['role' => 'user', 'content' => 'Summarise the pipeline.']];
        });
    }
}
