<x-layouts.app
    :title="'AI Providers — Setup'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'AI Providers'], ['label' => 'Setup']]">

    <div class="mx-auto max-w-5xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Connect an AI Provider</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pick a provider, paste your API key and save. Keys are encrypted at rest server-side (Laravel Crypto using the APP_KEY),
                    masked everywhere in the UI and never written to logs or config.
                </p>
            </div>
            <a href="{{ route('admin.ai-providers.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                ← Back to Providers
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($uiProviders as $key => $meta)
                @php $existing = $providers->get($key); @endphp
                <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $meta['label'] }}</h2>
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-300">{{ $key }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $meta['description'] }}</p>
                        @if (! empty($meta['docs_url']))
                            <a href="{{ $meta['docs_url'] }}" target="_blank" rel="noopener" class="mt-1 inline-block text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                                Get an API key →
                            </a>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.ai-providers.store') }}" class="space-y-4 p-5">
                        @csrf
                        <input type="hidden" name="provider" value="{{ $key }}">

                        @if ($existing)
                            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-900/60">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Configured key</p>
                                <p class="mt-1 font-mono text-sm text-gray-800 dark:text-gray-200">{{ $existing->maskedKey() ?? '****' }}</p>

                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs font-medium text-blue-600 dark:text-blue-400">Replace key</summary>
                                    <div class="mt-2">
                                        <label class="block text-sm text-gray-700 dark:text-gray-300">New API key ({{ $meta['key_help'] }})</label>
                                        <div class="mt-1 flex items-center gap-2">
                                            <input type="password" name="api_key" id="key-{{ $key }}" placeholder="sk-…"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                            <button type="button" data-target="key-{{ $key }}"
                                                    class="toggle-key shrink-0 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                                Show
                                            </button>
                                        </div>
                                    </div>
                                </details>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" onclick="document.getElementById('rotate-{{ $key }}').submit()"
                                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                        Rotate stored key
                                    </button>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">API key</label>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $meta['key_help'] }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <input type="password" name="api_key" id="key-{{ $key }}" placeholder="Paste API key"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                    <button type="button" data-target="key-{{ $key }}"
                                            class="toggle-key shrink-0 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                        Show
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">
                                Model
                                @if (! empty($meta['models']))
                                    <select name="model" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        <option value="{{ $existing->model ?? $meta['default_model'] }}">{{ $existing->model ?? $meta['default_model'] }}</option>
                                        @foreach ($meta['models'] as $m)
                                            @if ($m !== ($existing->model ?? $meta['default_model']))
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" name="model" value="{{ $existing->model ?? '' }}" placeholder="e.g. gpt-4o-mini"
                                           class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                @endif
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Base URL</label>
                                <input type="text" name="base_url" value="{{ $existing->base_url ?? (($key === 'generic') ? 'https://api.openai.com/v1/chat/completions' : '') }}"
                                       placeholder="{{ ($key === 'generic') ? 'https://your-endpoint/v1/chat/completions' : 'optional override' }}"
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Path</label>
                                <input type="text" name="path" value="{{ $existing->path ?? (($key === 'generic') ? '/v1/chat/completions' : '') }}"
                                       placeholder="/v1/chat/completions"
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Auth mode</label>
                            <select name="auth_mode" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="bearer" @selected(($existing->auth_mode ?? 'bearer') === 'bearer')>Bearer token (Authorization header)</option>
                                <option value="rapidapi" @selected(($existing->auth_mode ?? '') === 'rapidapi')>RapidAPI (x-rapidapi-key + host)</option>
                                <option value="google" @selected($key === 'gemini' || ($existing->auth_mode ?? '') === 'google')>Google (X-Goog-Api-Key)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Per-user daily limit</label>
                                <input type="number" name="per_user_daily_limit" value="{{ $existing->per_user_daily_limit }}" min="0" placeholder="unlimited"
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Per-user monthly limit</label>
                                <input type="number" name="per_user_monthly_limit" value="{{ $existing->per_user_monthly_limit }}" min="0" placeholder="unlimited"
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Org daily limit</label>
                                <input type="number" name="org_daily_limit" value="{{ $existing->org_daily_limit }}" min="0" placeholder="unlimited"
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Org monthly limit</label>
                                <input type="number" name="org_monthly_limit" value="{{ $existing->org_monthly_limit }}" min="0" placeholder="unlimited"
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="enabled" value="1" @checked($existing?->enabled ?? true) class="rounded border-gray-300"> Enabled
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="fallback_enabled" value="1" @checked($existing?->fallback_enabled ?? true) class="rounded border-gray-300"> Fallback provider
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                            {{ $existing ? 'Save ' . $existing->provider . ' provider' : 'Save ' . $key . ' provider' }}
                        </button>

                        <p class="text-[11px] text-gray-400 dark:text-gray-500">
                            The API key is encrypted at rest server-side and only ever shown masked (last 4 characters).
                        </p>
                    </form>

                    @if ($existing)
                        <form id="rotate-{{ $key }}" method="POST" action="{{ route('admin.ai-providers.rotate', $existing->id) }}" class="hidden">@csrf</form>
                        <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
                            <form method="POST" action="{{ route('admin.ai-providers.test', $existing->id) }}" class="flex items-center">
                                @csrf
                                <button type="submit" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                    Test connection — lightweight config check (no API call)
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <p class="text-sm text-blue-800 dark:text-blue-300">
                <span class="font-semibold">Why not .env?</span> Provider keys configured here live encrypted in the database, are scoped to this
                company, can be rotated from the UI, and require no <code class="rounded bg-blue-100 px-1 text-xs dark:bg-blue-900">config:clear</code>.
                The backend merges them into the AI gateway at request time.
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.toggle-key').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.getElementById(btn.dataset.target);
                    if (! input) { return; }
                    var reveal = input.type === 'password';
                    input.type = reveal ? 'text' : 'password';
                    btn.textContent = reveal ? 'Hide' : 'Show';
                });
            });
        </script>
    @endpush
</x-layouts.app>