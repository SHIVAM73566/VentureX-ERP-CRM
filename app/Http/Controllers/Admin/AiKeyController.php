<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Services\Ai\AiCredentialService;
use App\Services\Ai\AiProviderManager;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Zero-config, key-only onboarding for hosted AI providers.
 *
 * The user pastes one key. Base URL, path, auth mode and default model are
 * taken from config/ai.php so no endpoint fields are ever exposed or accepted
 * from the browser. Keys are encrypted at rest, fingerprinted, masked, never
 * flashed back, and every write requires step-up authentication.
 */
class AiKeyController extends Controller
{
    /**
     * Providers that can be connected with a key alone. Prefix rules reject a
     * key that belongs to a different vendor before it reaches storage.
     */
    protected const PROVIDERS = [
        'nvidia' => [
            'label' => 'NVIDIA NIM',
            'prefix' => 'nvapi-',
            'rule' => 'starts_with:nvapi-',
        ],
        'gemini' => [
            'label' => 'Google Gemini',
            'prefix' => 'AIza',
            'rule' => 'starts_with:AIza',
        ],
        'openai' => [
            'label' => 'OpenAI',
            'prefix' => 'sk-',
            'rule' => 'regex:/^sk-(?!ant-)/',
        ],
        'anthropic' => [
            'label' => 'Anthropic Claude',
            'prefix' => 'sk-ant-',
            'rule' => 'starts_with:sk-ant-',
        ],
    ];

    public function __construct(
        protected AiCredentialService $credentials,
        protected AiProviderManager $providers,
    ) {}

    /**
     * Save (or replace) a provider key for the current company.
     */
    public function store(Request $request, string $provider): RedirectResponse
    {
        $this->authorize('configure');
        $meta = $this->meta($provider);

        $rules = ['required', 'string', 'min:20', 'max:500', $meta['rule']];

        $data = $request->validate([
            'api_key' => $rules,
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $companyId = $this->companyId();
        $apiKey = trim($data['api_key']);
        $config = config('ai.providers.'.$provider, []);
        $ui = config('ai.ui_providers.'.$provider, []);

        $allowedModels = (array) ($ui['models'] ?? []);
        $requestedModel = $data['model'] ?? null;
        $model = ($requestedModel && in_array($requestedModel, $allowedModels, true))
            ? $requestedModel
            : ($ui['default_model'] ?? $config['model'] ?? null);

        $row = $this->findProvider($companyId, $provider, withTrashed: true);

        if ($row === null) {
            $row = new AiProvider;
            $row->company_id = $companyId;
            $row->provider = $provider;
            $row->created_by = auth()->id();
        } elseif ($row->trashed()) {
            $row->restore();
        }

        $row->forceFill([
            'encrypted_key' => $this->credentials->encryptKey($apiKey),
            'key_hash' => $this->credentials->hashKey($apiKey),
            'model' => $model,
            // Endpoints are hardcoded server-side; never accepted from input.
            'base_url' => $config['base_url'] ?? null,
            'path' => $config['path'] ?? null,
            'auth_mode' => $config['auth_mode'] ?? 'bearer',
            'enabled' => true,
        ]);
        $row->save();

        AuditLogger::log('ai_provider_saved', 'ai_providers', $row, null, [
            'provider' => $provider,
            'model' => $row->model,
            'enabled' => true,
            'key_configured' => true,
            'key_tail' => substr($apiKey, -4),
            'source' => 'admin.settings',
        ]);

        return redirect()
            ->route('admin.settings.index', ['tab' => 'ai'])
            ->with('success', $meta['label'].' key saved. It is encrypted at rest and only ever shown masked (last 4 characters).');
    }

    /**
     * Remove a stored provider key. The row is soft-deleted so support can
     * restore it, while the key immediately stops being used.
     */
    public function destroy(string $provider): RedirectResponse
    {
        $this->authorize('configure');
        $meta = $this->meta($provider);

        $row = $this->findProvider($this->companyId(), $provider);

        if ($row === null) {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'ai'])
                ->with('error', 'No '.$meta['label'].' key is stored for this company.');
        }

        $row->delete();

        AuditLogger::log('ai_provider_deleted', 'ai_providers', null, null, [
            'provider' => $provider,
            'source' => 'admin.settings',
        ]);

        return redirect()
            ->route('admin.settings.index', ['tab' => 'ai'])
            ->with('success', $meta['label'].' key removed. The AI engine will fall back to other configured providers.');
    }

    /**
     * Verify the stored key decrypts and the provider is wired into runtime
     * config. No network call, so it is deterministic and test-safe.
     */
    public function test(string $provider): RedirectResponse
    {
        $this->authorize('configure');
        $meta = $this->meta($provider);

        $row = $this->findProvider($this->companyId(), $provider);

        if ($row === null) {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'ai'])
                ->with('error', 'Add a '.$meta['label'].' key first, then run the connection check.');
        }

        $key = $row->getDecryptedKey();

        if ($key === null || $key === '') {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'ai'])
                ->with('error', 'The stored '.$meta['label'].' key could not be decrypted. Save a new key to recover.');
        }

        $this->providers->syncForCurrentContext();

        $config = config('ai.providers.'.$provider);

        if (! is_array($config)) {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'ai'])
                ->with('error', $meta['label'].' is not defined in config/ai.php. Restore the default provider config.');
        }

        $pieces = [
            'key decrypts ('.strlen($key).' chars, tail '.substr($key, -4).')',
            'auth mode: '.($row->auth_mode ?? $config['auth_mode'] ?? 'n/a'),
            'model: '.($row->model ?? $config['model'] ?? 'n/a'),
            'endpoint: '.($row->base_url ?? $config['base_url'] ?? 'n/a').($row->path ?? $config['path'] ?? ''),
        ];

        AuditLogger::log('ai_provider_test', 'ai_providers', $row, null, [
            'provider' => $provider,
            'result' => 'ok',
            'source' => 'admin.settings',
        ]);

        return redirect()
            ->route('admin.settings.index', ['tab' => 'ai'])
            ->with('success', $meta['label'].' configuration check passed — '.implode(', ', $pieces).'.');
    }

    protected function meta(string $provider): array
    {
        abort_unless(isset(self::PROVIDERS[$provider]), 404);

        return self::PROVIDERS[$provider];
    }

    protected function companyId(): int
    {
        $companyId = CompanyContext::id();

        abort_if($companyId === null, 403, 'No company context available.');

        return (int) $companyId;
    }

    protected function findProvider(int $companyId, string $provider, bool $withTrashed = false): ?AiProvider
    {
        $query = $withTrashed ? AiProvider::withTrashed() : AiProvider::query();

        return $query
            ->where('company_id', $companyId)
            ->where('provider', $provider)
            ->first();
    }
}
