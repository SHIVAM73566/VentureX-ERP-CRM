<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseToken;
use App\Services\Ai\AiProviderManager;
use App\Services\Ai\AiUsageService;
use App\Services\Ai\SuperBrainShield;
use App\Services\AuditLogger;
use App\Services\Billing\StripeBillingPortalService;
use App\Services\BrandingService;
use App\Services\ClientLicenseService;
use App\Services\CompanyContext;
use App\Services\EntitlementService;
use App\Services\SettingService;
use App\Services\UploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settings,
        protected AiProviderManager $aiProviders,
        protected BrandingService $branding,
        protected UploadSecurityService $uploads,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('configure');

        $tab = $request->query('tab', 'company');

        $stored = $this->aiProviders->storedProviders();

        $cards = [];
        foreach ((array) config('ai.ui_providers', []) as $provider => $meta) {
            if (! in_array($provider, ['nvidia', 'gemini', 'openai', 'anthropic'], true)) {
                continue;
            }

            $row = $stored->get($provider);

            $cards[$provider] = [
                'provider' => $provider,
                'label' => $meta['label'] ?? ucfirst($provider),
                'description' => $meta['description'] ?? '',
                'key_help' => $meta['key_help'] ?? '',
                'docs_url' => $meta['docs_url'] ?? null,
                'default_model' => $meta['default_model'] ?? null,
                'configured' => $row !== null && filled($row->encrypted_key),
                'enabled' => (bool) ($row?->enabled ?? false),
                'masked' => $row?->maskedKey(),
                'model' => $row?->model ?? ($meta['default_model'] ?? null),
                'updated_at' => $row?->updated_at,
            ];
        }

        $layers = config('layers.layers', []);
        $entitledKeys = app(EntitlementService::class)->activeLayerKeys();
        $checkoutOffers = config('layers.checkout_offers', []);
        $companyId = CompanyContext::id();
        $hasStripeCustomer = $companyId !== null
            && app(StripeBillingPortalService::class)->hasCustomer($companyId);
        $billingStatus = $this->billingStatus($hasStripeCustomer, $layers);

        $aiUsageToday = 0;
        $aiUsageMonth = 0;
        try {
            $svc = app(AiUsageService::class);
            $aiUsageToday = $svc->dailyCount(null, null);
            $aiUsageMonth = $svc->monthlyCount(null, null);
        } catch (\Throwable $e) {
            $aiUsageToday = 0;
            $aiUsageMonth = 0;
        }

        $licenseToken = null;
        if ($companyId !== null) {
            $licenseToken = LicenseToken::query()->where('company_id', $companyId)->first();
        }

        return response()
            ->view('admin.settings.index', [
                'tab' => $tab,
                'settings' => $this->settings->all(),
                'branding' => $this->branding->all(),
                'aiKeyCards' => $cards,
                'shieldStatus' => app(SuperBrainShield::class)->status(),
                'nvidiaMeta' => config('ai.ui_providers.nvidia', []),
                'layersList' => $layers,
                'checkoutOffers' => $checkoutOffers,
                'entitledKeys' => $entitledKeys,
                'hasStripeCustomer' => $hasStripeCustomer,
                'billingStatus' => $billingStatus,
                'aiUsageToday' => $aiUsageToday,
                'aiUsageMonth' => $aiUsageMonth,
                'licenseToken' => $licenseToken,
            ])
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
    }

    /**
     * Describe billing readiness for the plan tab using only booleans, env
     * variable names and the portal return URL. No secret or Price ID value is
     * ever handed to the view or rendered into browser code.
     */
    protected function billingStatus(bool $hasStripeCustomer, array $layers): array
    {
        $secretConfigured = filled(config('services.stripe.secret'));
        $webhookConfigured = filled(config('services.stripe.webhook_secret'));
        $configuredPortalReturnUrl = trim((string) config('services.stripe.portal_return_url'));
        $portalReturnUrl = $configuredPortalReturnUrl !== ''
            ? $configuredPortalReturnUrl
            : route('admin.settings.index', ['tab' => 'plan']);

        $priceEnvByLayer = [];
        $missingPriceEnvs = [];
        $configuredPrices = 0;

        foreach ($layers as $key => $layer) {
            if (! is_string($key) || ! is_array($layer)) {
                continue;
            }

            $envName = 'STRIPE_PRICE_'.Str::upper($key);
            $priceEnvByLayer[$key] = $envName;

            if (filled(config('services.stripe.prices.'.$key))) {
                $configuredPrices++;
            } else {
                $missingPriceEnvs[] = $envName;
            }
        }

        return [
            'secret_configured' => $secretConfigured,
            'webhook_configured' => $webhookConfigured,
            'portal_return_url' => $portalReturnUrl,
            'portal_return_url_configured' => $configuredPortalReturnUrl !== '',
            'price_env_by_layer' => $priceEnvByLayer,
            'missing_price_envs' => $missingPriceEnvs,
            'prices_configured' => $configuredPrices,
            'prices_total' => count($layers),
            'checkout_ready' => $secretConfigured && $configuredPrices > 0,
            'webhook_ready' => $secretConfigured && $webhookConfigured,
            'portal_ready' => $secretConfigured && $hasStripeCustomer,
        ];
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('configure');

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ]);

        // Allowlist: only known application settings may be persisted.
        $allowed = [
            'ai.provider', 'ai.model', 'ai.temperature', 'ai.top_p', 'ai.max_tokens',
            'currency.default', 'format.date', 'fiscal.start', 'alerts.email',
        ];

        $updated = [];
        $oldValues = [];
        $newValues = [];

        foreach ($validated['settings'] as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }

            $oldValue = $this->settings->get($key);
            $newValue = $value === '' ? null : $value;

            $this->settings->set($key, $newValue);
            $updated[] = $key;
            $oldValues[$key] = $oldValue;
            $newValues[$key] = $newValue;
        }

        if (! $updated) {
            return back()->with('error', 'No allowed settings were submitted.');
        }

        AuditLogger::log('update', 'settings', null, $oldValues, $newValues);

        return back()->with('success', 'Settings saved.');
    }

    /**
     * Persist company branding (logo, typography, tax-compliant invoice layout).
     */
    public function updateBranding(Request $request): RedirectResponse
    {
        $this->authorize('configure');

        $allowedFonts = [
            'Helvetica, Arial, sans-serif',
            'Georgia, Times New Roman, serif',
            'Courier New, monospace',
            'DejaVu Sans, sans-serif',
            'DejaVu Serif, serif',
        ];

        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'accent_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'font_family' => ['nullable', 'string', Rule::in($allowedFonts)],
            'invoice_footer' => ['nullable', 'string', 'max:500'],
            'invoice_terms' => ['nullable', 'string', 'max:2000'],
            'watermark_text' => ['nullable', 'string', 'max:50'],
            'show_watermark' => ['nullable', 'boolean'],
            'remove_logo' => ['nullable', 'boolean'],
            // Raster only: SVG can carry script and is never accepted as a logo.
            'logo' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,image/webp', 'max:2048'],
        ]);

        $fields = [
            'company_name', 'legal_name', 'tagline', 'email', 'phone', 'website',
            'address', 'tax_id', 'registration_number', 'accent_color', 'font_family',
            'invoice_footer', 'invoice_terms', 'watermark_text',
        ];

        $old = $this->branding->all();
        $oldValues = [];
        $newValues = [];

        foreach ($fields as $field) {
            $oldValue = $old[$field] ?? null;
            $newValue = $validated[$field] ?? null;

            if ($newValue === '') {
                $newValue = null;
            }

            $this->settings->set('branding.'.$field, $newValue, ['group' => 'branding']);
            $oldValues['branding.'.$field] = $oldValue;
            $newValues['branding.'.$field] = $newValue;
        }

        $showWatermark = $request->boolean('show_watermark');
        $this->settings->set('branding.show_watermark', $showWatermark ? '1' : '0', ['group' => 'branding']);
        $oldValues['branding.show_watermark'] = $old['show_watermark'] ?? true;
        $newValues['branding.show_watermark'] = $showWatermark;

        if ($request->boolean('remove_logo')) {
            $this->deleteLogo($old['logo_path'] ?? null);
            $this->settings->set('branding.logo_path', null, ['group' => 'branding']);
            $oldValues['branding.logo_path'] = $old['logo_path'] ?? null;
            $newValues['branding.logo_path'] = null;
        } elseif ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $this->uploads->validateImage($file, ['png', 'jpg', 'jpeg', 'webp']);

            $this->deleteLogo($old['logo_path'] ?? null);
            $path = $file->storeAs(
                'branding/'.(CompanyContext::id() ?? 'shared'),
                Str::uuid()->toString().'.'.$extension,
                'public'
            );
            $this->settings->set('branding.logo_path', $path, ['group' => 'branding']);
            $oldValues['branding.logo_path'] = $old['logo_path'] ?? null;
            $newValues['branding.logo_path'] = $path;
        }

        AuditLogger::log('update', 'branding', null, $oldValues, $newValues);

        return redirect()
            ->route('admin.settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding saved.');
    }

    public function applyLicenseToken(Request $request, ClientLicenseService $licenseService): RedirectResponse
    {
        $this->authorize('configure');

        $validated = $request->validate([
            'license_token' => ['required', 'string', 'max:20000'],
        ]);

        $companyId = CompanyContext::id();
        if (! $companyId) {
            return back()->with('error', 'No company context available.');
        }

        $result = $licenseService->applyToken($companyId, trim($validated['license_token']));

        AuditLogger::log('update', 'license_token', null, ['action' => 'apply_token'], ['ok' => $result['ok']]);

        if (! $result['ok']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect()->route('admin.settings.index', ['tab' => 'plan'])->with('success', $result['message']);
    }

    public function syncLicense(Request $request, ClientLicenseService $licenseService): RedirectResponse
    {
        $this->authorize('configure');

        $companyId = CompanyContext::id();
        if (! $companyId) {
            return back()->with('error', 'No company context available.');
        }

        $result = $licenseService->syncFromServer($companyId);

        AuditLogger::log('update', 'license_token', null, ['action' => 'sync_server'], ['ok' => $result['ok']]);

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('admin.settings.index', ['tab' => 'plan'])->with('success', $result['message']);
    }

    protected function deleteLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
