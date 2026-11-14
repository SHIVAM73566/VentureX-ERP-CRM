<x-layouts.app title="Settings" :breadcrumbs="[['label' => 'Administration'], ['label' => 'Settings']]">

    <div class="mb-4 flex gap-1 rounded-lg bg-ink-100 p-1">
        <a href="{{ route('admin.settings.index', ['tab' => 'ai']) }}" class="tab {{ $tab === 'ai' ? 'tab-active' : '' }}">AI Engine</a>
        <a href="{{ route('admin.settings.index', ['tab' => 'company']) }}" class="tab {{ $tab === 'company' ? 'tab-active' : '' }}">Company</a>
        <a href="{{ route('admin.settings.index', ['tab' => 'branding']) }}" class="tab {{ $tab === 'branding' ? 'tab-active' : '' }}">Branding &amp; Invoices</a>
    </div>

    @if ($tab === 'ai')
        <div class="mx-auto max-w-5xl space-y-6">

            {{-- ============================================================
                 AERO X Super Intelligence Brain — shield status
            ============================================================ --}}
            <div class="card space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-bold text-ink-900">
                            AERO X Super Intelligence Brain
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Security shield</span>
                        </h2>
                        <p class="text-sm text-ink-400">
                            Activates automatically as soon as a provider key is saved. Every AI request passes through four layers before and after it reaches a model.
                        </p>
                    </div>
                    <span class="badge {{ $shieldStatus['active'] ? 'badge-green' : 'badge-gray' }}">
                        {{ $shieldStatus['active'] ? 'Active' : 'Standby' }}
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($shieldStatus['layers'] as $layer)
                        <div class="rounded-lg border border-ink-200 p-3 dark:border-ink-800">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $layer['name'] }}</p>
                                <span class="badge {{ $layer['state'] === 'active' ? 'badge-green' : ($layer['state'] === 'off' ? 'badge-gray' : 'badge-amber') }}">
                                    {{ ucfirst($layer['state']) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">{{ $layer['detail'] }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-ink-400">
                    {{ $shieldStatus['providers'] }} provider {{ \Illuminate\Support\Str::plural('key', $shieldStatus['providers']) }} encrypted for this company.
                    Prompt text is never written to logs — only redaction and block metadata.
                </p>
            </div>

            {{-- ============================================================
                 Key-only onboarding: one input per provider, no endpoints.
            ============================================================ --}}
            <div>
                <div class="mb-3">
                    <h2 class="text-lg font-bold text-ink-900">Connect an AI provider</h2>
                    <p class="text-sm text-ink-400">
                        Paste the key once. Base URLs, API paths, auth mode and default models are built in — there is nothing else to configure.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($aiKeyCards as $card)
                        @php($provider = $card['provider'])
                        <div class="card space-y-4" x-data="{ reveal: false }">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-base font-bold text-ink-900">{{ $card['label'] }}</h3>
                                    <p class="mt-0.5 text-xs leading-relaxed text-ink-400">{{ $card['description'] }}</p>
                                </div>
                                @if ($card['configured'])
                                    <span class="badge {{ $card['enabled'] ? 'badge-green' : 'badge-amber' }}">
                                        {{ $card['enabled'] ? 'Connected' : 'Saved — disabled' }}
                                    </span>
                                @else
                                    <span class="badge badge-red">Not connected</span>
                                @endif
                            </div>

                            @if ($card['configured'])
                                <div class="rounded-lg bg-ink-50 px-3 py-2 text-xs dark:bg-ink-800/50">
                                    <p class="font-mono text-ink-800 dark:text-ink-100">{{ $card['masked'] ?? '****' }}</p>
                                    <p class="mt-1 break-all text-ink-400">{{ $card['model'] }}</p>
                                    <p class="mt-0.5 text-ink-400">Updated {{ $card['updated_at']?->diffForHumans() ?? '—' }}</p>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.settings.ai-key.store', $provider) }}" autocomplete="off" class="space-y-3">
                                @csrf
                                <input type="hidden" name="card" value="{{ $provider }}">

                                <div>
                                    <label for="key-{{ $provider }}" class="label">API key</label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            id="key-{{ $provider }}"
                                            name="api_key"
                                            :type="reveal ? 'text' : 'password'"
                                            autocomplete="new-password"
                                            autocapitalize="off"
                                            autocorrect="off"
                                            spellcheck="false"
                                            inputmode="text"
                                            data-lpignore="true"
                                            data-1p-ignore
                                            placeholder="{{ $card['configured'] ? 'Paste a new key to replace it' : ($provider === 'nvidia' ? 'nvapi-…' : ($provider === 'gemini' ? 'AIza…' : 'sk-…')) }}"
                                            class="input font-mono"
                                            @error('api_key') aria-invalid="true" @enderror
                                            {{ $card['configured'] ? '' : 'required' }}
                                        >
                                        <button type="button" @click="reveal = ! reveal" x-text="reveal ? 'Hide' : 'Show'"
                                                class="shrink-0 rounded-lg border border-ink-200 px-3 py-2 text-xs font-medium text-ink-600 hover:bg-ink-100 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800">
                                            Show
                                        </button>
                                    </div>

                                    @if (old('card') === $provider)
                                        @error('api_key')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif

                                    <p class="mt-1 text-[11px] text-ink-400">{{ $card['key_help'] }}</p>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-[11px] text-ink-400">Default model: <span class="font-mono">{{ $card['default_model'] }}</span></p>
                                    <button type="submit" class="btn-accent text-xs">
                                        {{ $card['configured'] ? 'Replace key' : 'Connect' }}
                                    </button>
                                </div>
                            </form>

                            <div class="flex flex-wrap items-center gap-2 border-t border-ink-100 pt-3 dark:border-ink-700">
                                @if ($card['docs_url'])
                                    <a href="{{ $card['docs_url'] }}" target="_blank" rel="noopener noreferrer"
                                       class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">Get a key</a>
                                @endif

                                @if ($card['configured'])
                                    <form method="POST" action="{{ route('admin.settings.ai-key.test', $provider) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-ink-200 px-2.5 py-1 text-[11px] font-medium text-ink-700 hover:bg-ink-100 dark:border-ink-700 dark:text-ink-200 dark:hover:bg-ink-800">
                                            Check
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.settings.ai-key.destroy', $provider) }}"
                                          onsubmit="return confirm('Remove the stored {{ $card['label'] }} key? AI requests will fall back to another provider.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-[11px] font-medium text-red-700 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ============================================================
                 AI engine defaults (advanced, optional)
            ============================================================ --}}
            <details class="card space-y-4">
                <summary class="cursor-pointer text-sm font-semibold text-ink-800 dark:text-ink-100">
                    AI engine defaults <span class="font-normal text-ink-400">— provider routing, temperature and token limits</span>
                </summary>

                <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.select label="Provider" name="settings[ai.provider]" :options="['nvidia' => 'NVIDIA NIM', 'gemini' => 'Google Gemini', 'openai' => 'OpenAI', 'anthropic' => 'Anthropic Claude', 'deepseek' => 'DeepSeek', 'swift' => 'Swift AI']" :value="$settings['ai.provider'] ?? config('ai.default_provider', 'nvidia')" />
                        <x-form.input label="Model" name="settings[ai.model]" :value="$settings['ai.model'] ?? config('ai.model', 'nvidia/llama-3.3-nemotron-super-49b-v1.5')" />
                        <x-form.input label="Temperature" name="settings[ai.temperature]" type="number" step="0.1" :value="$settings['ai.temperature'] ?? 0.4" />
                        <x-form.input label="Top P" name="settings[ai.top_p]" type="number" step="0.05" :value="$settings['ai.top_p'] ?? 0.9" />
                        <x-form.input label="Max Tokens" name="settings[ai.max_tokens]" type="number" :value="$settings['ai.max_tokens'] ?? 2048" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-accent">Save AI Settings</button>
                    </div>
                </form>
            </details>

            {{-- ============================================================
                 Shared security notes
            ============================================================ --}}
            <div class="rounded-lg bg-ink-50 p-4 text-xs text-ink-500 dark:bg-ink-800/50 dark:text-ink-400">
                <p class="font-semibold text-ink-700 dark:text-ink-200">How every key is protected</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    <li>Encrypted at rest with the application key; a keyed HMAC fingerprint detects reuse without decryption.</li>
                    <li>Only the last 4 characters are ever displayed. The full key is never stored in plaintext, logged, or flashed back on validation errors.</li>
                    <li>Every save, check, and removal requires step-up authentication (password/MFA verified within the last 5 minutes) and is rate limited.</li>
                    <li>Endpoint fields are never shown or accepted from the browser — base URLs and API paths come from server config only.</li>
                    <li>All changes are written to the audit log with the key tail only; this page is served with <code class="rounded bg-ink-100 px-1 py-0.5 text-[11px]">Cache-Control: no-store</code>.</li>
                </ul>
            </div>

            <p class="text-xs text-ink-400">
                Need per-provider quotas, rotation history, or a custom OpenAI-compatible endpoint?
                <a href="{{ route('admin.ai-providers.index') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Open the full AI provider console</a>.
                Local Ollama requires no key — set <code class="rounded bg-ink-100 px-1 py-0.5 text-[11px]">OLLAMA_BASE_URL</code> in <code class="rounded bg-ink-100 px-1 py-0.5 text-[11px]">.env</code> to use it.
            </p>
        </div>
    @endif

    @if ($tab === 'company')
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mx-auto max-w-3xl space-y-6">
            @csrf
            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Company Preferences</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Default Currency" name="settings[currency.default]" :value="$settings['currency.default'] ?? 'INR'" />
                    <x-form.input label="Date Format" name="settings[format.date]" :value="$settings['format.date'] ?? 'd M Y'" />
                    <x-form.input label="Fiscal Year Start (MM-DD)" name="settings[fiscal.start]" :value="$settings['fiscal.start'] ?? '04-01'" />
                    <x-form.input label="Alert Email" name="settings[alerts.email]" type="email" :value="$settings['alerts.email'] ?? ''" />
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-accent">Save Company Settings</button>
            </div>
        </form>
    @endif

    @if ($tab === 'branding')
        <form method="POST" action="{{ route('admin.settings.branding') }}" enctype="multipart/form-data" class="mx-auto max-w-4xl space-y-6">
            @csrf

            <div class="card space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Invoice branding</h2>
                        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Controls the logo, colours, typography and legal footer on every invoice and quotation PDF. Values fall back to the company record.</p>
                    </div>
                    @if ($branding['logo_url'])
                        <img src="{{ $branding['logo_url'] }}" alt="Current logo" class="h-14 w-auto max-w-[10rem] rounded-lg border border-ink-100 bg-white object-contain p-1 dark:border-ink-700">
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Display name" name="company_name" :value="$branding['company_name']" help="Shown in the invoice header." />
                    <x-form.input label="Legal / registered name" name="legal_name" :value="$branding['legal_name']" />
                    <x-form.input label="Tagline" name="tagline" :value="$branding['tagline']" />
                    <x-form.input label="Billing email" name="email" type="email" :value="$branding['email']" />
                    <x-form.input label="Phone" name="phone" :value="$branding['phone']" />
                    <x-form.input label="Website" name="website" type="url" :value="$branding['website']" />
                </div>

                <x-form.textarea label="Registered address" name="address" :value="$branding['address']" rows="2" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Tax / VAT / GST ID" name="tax_id" :value="$branding['tax_id']" help="Printed on tax-compliant invoices." />
                    <x-form.input label="Company registration number" name="registration_number" :value="$branding['registration_number']" />
                </div>
            </div>

            <div class="card space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Visual identity</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="logo" class="label">Company logo</label>
                        <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="input py-2">
                        <p class="mt-1 text-xs text-ink-400">PNG, JPG, WEBP or SVG, up to 2 MB. Embedded into PDFs automatically.</p>
                        @error('logo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4">
                        <div>
                            <label for="accent_color" class="label">Accent colour</label>
                            <input id="accent_color" name="accent_color" type="color" value="{{ $branding['accent_color'] }}" class="h-10 w-full rounded-lg border border-ink-200 bg-white p-1 dark:border-ink-700 dark:bg-ink-800">
                        </div>
                        <x-form.select label="Invoice typography" name="font_family" :options="[
                            'Helvetica, Arial, sans-serif' => 'Helvetica / Arial (clean)',
                            'Georgia, Times New Roman, serif' => 'Georgia / Times (classic)',
                            'Courier New, monospace' => 'Courier (technical)',
                            'DejaVu Sans, sans-serif' => 'DejaVu Sans (PDF-safe)',
                            'DejaVu Serif, serif' => 'DejaVu Serif (PDF-safe)',
                        ]" :value="$branding['font_family']" :placeholder="null" />
                    </div>
                </div>

                @if ($branding['logo_path'])
                    <x-form.checkbox name="remove_logo" label="Remove the current logo" :checked="false" description="The invoice will fall back to a typographic wordmark." />
                @endif
            </div>

            <div class="card space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Invoice footer &amp; watermark</h3>

                <x-form.textarea label="Footer message" name="invoice_footer" :value="$branding['invoice_footer']" rows="2" />
                <x-form.textarea label="Terms &amp; payment instructions" name="invoice_terms" :value="$branding['invoice_terms']" rows="3" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Watermark text" name="watermark_text" :value="$branding['watermark_text']" help="Leave blank to hide the watermark." />
                    <div class="flex items-end pb-1">
                        <x-form.checkbox name="show_watermark" label="Show watermark on PDFs" :checked="$branding['show_watermark']" description="Applies a subtle diagonal label behind the invoice body." />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-accent">Save Branding</button>
            </div>
        </form>
    @endif
</x-layouts.app>
