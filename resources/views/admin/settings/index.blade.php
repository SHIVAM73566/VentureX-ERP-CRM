<x-layouts.app title="Settings" :breadcrumbs="[['label' => 'Administration'], ['label' => 'Settings']]">

    <div class="mb-4 flex gap-1 rounded-lg bg-ink-100 p-1">
        <a href="{{ route('admin.settings.index', ['tab' => 'ai']) }}" class="tab {{ $tab === 'ai' ? 'tab-active' : '' }}">AI Engine</a>
        <a href="{{ route('admin.settings.index', ['tab' => 'company']) }}" class="tab {{ $tab === 'company' ? 'tab-active' : '' }}">Company</a>
    </div>

    @if ($tab === 'ai')
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mx-auto max-w-3xl space-y-6">
            @csrf
            <div class="card space-y-4">
                <div>
                    <h2 class="text-lg font-bold text-ink-900">AI Engine Configuration</h2>
                    <p class="text-sm text-ink-400">Applied to all skills for this company. Keys never leave the server.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Provider" name="settings[ai.provider]" :options="['swift' => 'Swift AI', 'gemini' => 'Google Gemini', 'deepseek' => 'DeepSeek', 'nvidia' => 'NVIDIA NIM (legacy)']" :value="$settings['ai.provider'] ?? config('ai.default_provider', 'swift')" />
                    <x-form.input label="Model" name="settings[ai.model]" :value="$settings['ai.model'] ?? config('ai.model', 'gpt-5')" />
                    <x-form.input label="Temperature" name="settings[ai.temperature]" type="number" step="0.1" :value="$settings['ai.temperature'] ?? 0.4" />
                    <x-form.input label="Top P" name="settings[ai.top_p]" type="number" step="0.05" :value="$settings['ai.top_p'] ?? 0.9" />
                    <x-form.input label="Max Tokens" name="settings[ai.max_tokens]" type="number" :value="$settings['ai.max_tokens'] ?? 2048" />
                </div>
                <p class="text-xs text-ink-400">API keys are managed in the server environment (RAPIDAPI_KEY). They are never stored in the database or shown in this screen.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-accent">Save AI Settings</button>
            </div>
        </form>
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
</x-layouts.app>
