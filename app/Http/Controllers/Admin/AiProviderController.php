<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\AiUsageLog;
use App\Models\AuditLog;
use App\Services\Ai\AiCredentialService;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AiProviderManager;
use App\Services\Ai\AiRateLimitService;
use App\Services\Ai\AiUsageService;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AiProviderController extends Controller
{
    public function __construct(
        protected AiProviderManager $providers,
        protected AiCredentialService $credentials,
        protected AiUsageService $usage,
        protected AiRateLimitService $rateLimit,
        protected AiGateway $gateway,
    ) {}

    /**
     * All provider cards (configured + available), limits and usage stats.
     */
    public function index(): View
    {
        $actingCompany = (int) auth()->user()->company_id;
        abort_if($actingCompany === 0, 403, 'Your account is not bound to a company.');
        $ownRows = AiProvider::query()->where('company_id', $actingCompany)->exists();
        if (! $ownRows && AiProvider::query()->exists()) {
            abort(403, 'This AI provider panel is scoped to another company.');
        }

        $this->authorizeConfigure();

        $this->providers->syncForCurrentContext();
        $stored = $this->providers->storedProviders();
        $uiProviders = config('ai.ui_providers', []);

        $cards = [];
        foreach (config('ai.providers', []) as $name => $cfg) {
            $row = $stored->get($name);
            $cards[$name] = $this->card($name, $cfg, $row, $uiProviders);
        }

        // Providers stored via the wizard that are not listed in config/ai.php.
        foreach ($stored as $name => $row) {
            if (! isset($cards[$name])) {
                $cards[$name] = $this->card($name, [], $row, $uiProviders);
            }
        }

        ksort($cards);

        $stats = [
            'providers' => $stored->count(),
            'enabled' => $stored->filter(fn ($p) => $p->enabled)->count(),
            'requests_today' => $this->usage->dailyCount(null),
            'requests_month' => $this->usage->monthlyCount(null),
        ];

        $envHasKeys = $this->envHasAnyApiKey();

        return view('admin.ai-providers.index', [
            'cards' => $cards,
            'stats' => $stats,
            'showSetupWizard' => $stored->isEmpty() && ! $envHasKeys,
            'dismissed' => (bool) Session::get('ai_providers_setup_dismissed', false),
            'uiProviders' => $uiProviders,
        ]);
    }

    /**
     * Create or update a provider (used by the setup wizard).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeConfigure();

        $data = $this->validateProvider($request);

        $companyId = CompanyContext::id();
        abort_if($companyId === null, 403, 'No company context available.');

        $provider = AiProvider::query()
            ->where('company_id', $companyId)
            ->where('provider', $data['provider'])
            ->first();

        $payload = $this->payload($request);

        if ($provider === null) {
            $provider = new AiProvider;
            $provider->company_id = $companyId;
            $provider->provider = $data['provider'];
            $provider->created_by = auth()->id();
        }

        if (! empty($data['api_key'])) {
            $payload['encrypted_key'] = $this->credentials->encryptKey($data['api_key']);
            $payload['key_hash'] = $this->credentials->hashKey($data['api_key']);
        }

        $provider->forceFill($payload);
        $provider->save();

        AuditLogger::log('ai_provider_saved', 'ai_providers', $provider, null, [
            'provider' => $provider->provider,
            'model' => $provider->model,
            'enabled' => $provider->enabled,
            'key_configured' => ! empty($data['api_key']),
        ]);

        return redirect()->route('admin.ai-providers.index')
            ->with('success', 'AI provider "'.$provider->provider.'" saved. Keys are encrypted at rest and never stored in plaintext.');
    }

    /**
     * Update limits, model, endpoint, auth mode and the enabled toggle.
     */
    public function update(Request $request, AiProvider $provider): RedirectResponse
    {
        $this->authorizeConfigure();
        $this->guardCompany($provider);

        $data = $this->validateProvider($request, false);
        $payload = $this->payload($request);

        if (! empty($data['api_key'])) {
            $payload['encrypted_key'] = $this->credentials->encryptKey($data['api_key']);
            $payload['key_hash'] = $this->credentials->hashKey($data['api_key']);
        }

        $provider->forceFill($payload);
        $provider->save();

        AuditLogger::log('ai_provider_updated', 'ai_providers', $provider, null, [
            'provider' => $provider->provider,
            'model' => $provider->model,
            'enabled' => $provider->enabled,
            'key_replaced' => ! empty($data['api_key']),
        ]);

        return back()->with('success', 'AI provider "'.$provider->provider.'" updated.');
    }

    /**
     * Soft-delete a provider. The stored key is removed with the row.
     */
    public function destroy(AiProvider $provider): RedirectResponse
    {
        $this->authorizeConfigure();
        $this->guardCompany($provider);

        $name = $provider->provider;
        $provider->delete();

        AuditLogger::log('ai_provider_deleted', 'ai_providers', null, null, ['provider' => $name]);

        return back()->with('success', 'AI provider "'.$name.'" removed.');
    }

    /**
     * Lightweight configuration check — verifies the stored key decrypts and
     * that the provider is wired into the gateway config. Never makes a real
     * provider API call; fails soft and surfaces a readable message.
     */
    public function test(Request $request, AiProvider $provider): RedirectResponse
    {
        $this->authorizeConfigure();
        $this->guardCompany($provider);

        $this->providers->syncForCurrentContext();

        $key = $provider->getDecryptedKey();
        if ($key === null || $key === '') {
            return back()->with('error', '"'.$provider->provider.'": no usable key stored (it could not be decrypted).');
        }

        $cfg = config("ai.providers.{$provider->provider}");
        if (! is_array($cfg)) {
            return back()->with('error', '"'.$provider->provider.'" is stored in the database but not defined in config/ai.php.');
        }

        $gatewayReady = $this->gateway->isEnabled();
        $routable = in_array($provider->provider, $this->gateway->availableProviders(), true);

        $pieces = [
            'key decrypts ('.strlen($key).' chars)',
            'auth mode: '.($provider->auth_mode ?? $cfg['auth_mode'] ?? 'n/a'),
            'model: '.($provider->model ?? $cfg['model'] ?? 'n/a'),
            'base_url: '.($provider->base_url ?? $cfg['base_url'] ?? 'n/a'),
        ];
        $pieces[] = $routable ? 'gateway: routable' : 'gateway: configured but not routable yet';
        $pieces[] = $gatewayReady ? 'gateway active' : 'no gateway provider active';

        AuditLogger::log('ai_provider_test', 'ai_providers', $provider, null, ['provider' => $provider->provider, 'result' => 'ok']);

        return back()->with('success', '"'.$provider->provider.'" config check passed — '.implode(', ', $pieces).'.');
    }

    /**
     * Re-encrypt the stored key with the current cipher (new ciphertext/IV).
     */
    public function rotate(Request $request, AiProvider $provider): RedirectResponse
    {
        $this->authorizeConfigure();
        $this->guardCompany($provider);

        if (empty($provider->encrypted_key)) {
            return back()->with('error', '"'.$provider->provider.'" has no stored key to rotate. Configure a key first.');
        }

        $plain = $this->credentials->decryptKey($provider->encrypted_key);
        if ($plain === null || $plain === '') {
            return back()->with('error', '"'.$provider->provider.'": stored key could not be decrypted before rotation.');
        }

        $provider->encrypted_key = $this->credentials->encryptKey($plain);
        $provider->key_hash = $this->credentials->hashKey($plain);
        $provider->save();

        AuditLogger::log('ai_provider_rotated', 'ai_providers', $provider, null, ['provider' => $provider->provider]);

        return back()->with('success', 'API key for "'.$provider->provider.'" was rotated (re-encrypted) server-side.');
    }

    public function enable(Request $request, AiProvider $provider): RedirectResponse
    {
        $this->authorizeConfigure();
        $this->guardCompany($provider);

        $provider->enabled = true;
        $provider->save();

        AuditLogger::log('ai_provider_enabled', 'ai_providers', $provider, null, ['provider' => $provider->provider]);

        return back()->with('success', '"'.$provider->provider.'" enabled.');
    }

    public function disable(Request $request, AiProvider $provider): RedirectResponse
    {
        $this->authorizeConfigure();
        $this->guardCompany($provider);

        $provider->enabled = false;
        $provider->save();

        AuditLogger::log('ai_provider_disabled', 'ai_providers', $provider, null, ['provider' => $provider->provider]);

        return back()->with('success', '"'.$provider->provider.'" disabled. Stored keys are kept but not used.');
    }

    /**
     * First-run setup wizard.
     */
    public function setup(): View
    {
        $this->authorizeConfigure();

        $uiProviders = config('ai.ui_providers', []);

        $generic = [
            'label' => 'Generic OpenAI-Compatible',
            'description' => 'Connect any OpenAI-compatible endpoint (Groq, Together, DreamHost, local vLLM / llama.cpp servers and others) that implements the chat completions API.',
            'key_help' => 'Paste your API key for the service. Stored encrypted at rest, never shown again in full.',
            'default_model' => '',
            'models' => [],
            'docs_url' => null,
        ];

        return view('admin.ai-providers.setup', [
            'providers' => $this->providers->storedProviders(),
            'uiProviders' => $uiProviders + ['generic' => $generic],
        ]);
    }

    /**
     * Dismiss the first-run setup banner (session only, non-blocking).
     */
    public function dismissSetup(): RedirectResponse
    {
        $this->authorizeConfigure();

        Session::put('ai_providers_setup_dismissed', true);

        return back()->with('success', 'AI setup banner dismissed. You can reopen it from the AI Providers page.');
    }

    /**
     * API Security dashboard — masked keys only, encryption status, usage and
     * audit surfacing. Plaintext keys are NEVER passed to the view.
     */
    public function security(): View
    {
        $this->authorizeConfigure();

        $stored = $this->providers->storedProviders();

        $providers = $stored->values()->map(function (AiProvider $provider) {
            return [
                'provider' => $provider->provider,
                'model' => $provider->model,
                'enabled' => $provider->enabled,
                'masked_key' => $provider->maskedKey(),
                'base_url' => $provider->base_url,
                'request_count' => $provider->request_count,
                'error_count' => $provider->error_count,
                'last_success_at' => $provider->last_success_at,
                'last_error_at' => $provider->last_error_at,
                'last_error' => $provider->last_error,
            ];
        })->values();

        $recentUsage = AiUsageLog::query()
            ->where('company_id', CompanyContext::id())
            ->latest('created_at')
            ->limit(20)
            ->get();

        $audits = AuditLog::query()
            ->with('user')
            ->where(function ($q) {
                $q->where('module', 'ai_providers')
                    ->orWhere('record_type', 'like', '%AiProvider%');
            })
            ->latest()
            ->limit(20)
            ->get();

        $encryption = [
            'at_rest' => config('ai.encrypted_keys_at_rest', true),
            'cipher' => config('app.cipher'),
            'app_key_available' => is_string(config('app.key')) && trim((string) config('app.key')) !== '',
        ];

        return view('admin.ai-providers.security', [
            'providers' => $providers,
            'usage' => [
                'today' => $this->usage->dailyCount(null),
                'month' => $this->usage->monthlyCount(null),
            ],
            'recentUsage' => $recentUsage,
            'audits' => $audits,
            'encryption' => $encryption,
            'healthPolicy' => config('ai.health'),
        ]);
    }

    /**
     * @return array{provider: string, api_key: ?string, model: ?string, base_url: ?string, path: ?string, auth_mode: ?string, per_user_daily_limit: ?int, per_user_monthly_limit: ?int, org_daily_limit: ?int, org_monthly_limit: ?int}
     */
    protected function validateProvider(Request $request, bool $requireProvider = true): array
    {
        $rules = [
            'api_key' => ['nullable', 'string', 'max:1000'],
            'model' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'string', 'max:500'],
            'path' => ['nullable', 'string', 'max:500'],
            'auth_mode' => ['nullable', 'string', 'in:bearer,rapidapi,google'],
            'enabled' => ['nullable', 'boolean'],
            'fallback_enabled' => ['nullable', 'boolean'],
            'per_user_daily_limit' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'per_user_monthly_limit' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'org_daily_limit' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'org_monthly_limit' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];

        if ($requireProvider) {
            $rules['provider'] = ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/'];
        }

        return $request->validate($rules);
    }

    /**
     * Provider fields that may be persisted from either the wizard or the table.
     */
    protected function payload(Request $request): array
    {
        $data = $request->all();

        return [
            'model' => $data['model'] ?? null,
            'base_url' => $data['base_url'] ?? null,
            'path' => $data['path'] ?? null,
            'auth_mode' => $data['auth_mode'] ?? null,
            'enabled' => $request->boolean('enabled'),
            'fallback_enabled' => $request->boolean('fallback_enabled'),
            'per_user_daily_limit' => $data['per_user_daily_limit'] ?? null,
            'per_user_monthly_limit' => $data['per_user_monthly_limit'] ?? null,
            'org_daily_limit' => $data['org_daily_limit'] ?? null,
            'org_monthly_limit' => $data['org_monthly_limit'] ?? null,
        ];
    }

    protected function envHasAnyApiKey(): bool
    {
        foreach (config('ai.providers', []) as $cfg) {
            if (! empty($cfg['api_key'] ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function card(string $name, array $cfg, ?AiProvider $row, array $uiProviders): array
    {
        $label = $uiProviders[$name]['label'] ?? ($row?->provider ?? ucfirst($name));

        return [
            'provider' => $name,
            'label' => $label,
            'config' => $cfg,
            'stored' => $row,
            'has_key' => $row?->encrypted_key !== null && $row?->encrypted_key !== '',
            'masked' => $row?->maskedKey(),
            'limits' => $this->rateLimit->effectiveLimits($name),
            'has_env_key' => ! empty($cfg['api_key'] ?? ''),
        ];
    }

    protected function guardCompany(AiProvider $provider): void
    {
        if ($provider->company_id !== CompanyContext::id()) {
            abort(403, 'This AI provider belongs to another company.');
        }
    }

    protected function authorizeConfigure(): void
    {
        $user = auth()->user();

        // Access is role-gated by the router (role:super_admin) and the nav
        // (hasRole). We avoid hasPermissionTo() here because Spatie throws when
        // the ai.configure permission row is absent from the database (fresh
        // installs / seeded DBs), which would 500 the whole page.
        if (! $user || (! $user->hasRole('super_admin'))) {
            abort(403, 'You do not have permission to configure AI providers.');
        }
    }
}