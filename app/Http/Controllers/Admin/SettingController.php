<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiProviderManager;
use App\Services\Ai\SuperBrainShield;
use App\Services\AuditLogger;
use App\Services\BrandingService;
use App\Services\CompanyContext;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settings,
        protected AiProviderManager $aiProviders,
        protected BrandingService $branding,
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

        return response()
            ->view('admin.settings.index', [
                'tab' => $tab,
                'settings' => $this->settings->all(),
                'branding' => $this->branding->all(),
                'aiKeyCards' => $cards,
                'shieldStatus' => app(SuperBrainShield::class)->status(),
                'nvidiaMeta' => config('ai.ui_providers.nvidia', []),
            ])
            ->withHeaders([
                // The panel shows credential metadata; never let a proxy or
                // browser cache the rendered page.
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
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
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
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
            $this->deleteLogo($old['logo_path'] ?? null);
            $path = $request->file('logo')->store('branding/'.(CompanyContext::id() ?? 'shared'), 'public');
            $this->settings->set('branding.logo_path', $path, ['group' => 'branding']);
            $oldValues['branding.logo_path'] = $old['logo_path'] ?? null;
            $newValues['branding.logo_path'] = $path;
        }

        AuditLogger::log('update', 'branding', null, $oldValues, $newValues);

        return redirect()
            ->route('admin.settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding saved.');
    }

    protected function deleteLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
