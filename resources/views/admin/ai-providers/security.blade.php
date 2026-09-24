<x-layouts.app
    :title="'AI Security'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'AI Providers'], ['label' => 'API Security']]">

    <div class="mx-auto max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">API Security Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Oversight of every AI provider credential, its encryption state and recent usage. Keys are never shown in plaintext.
                </p>
            </div>
            <a href="{{ route('admin.ai-providers.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                ← Back to Providers
            </a>
        </div>

        {{-- Red secret notice --}}
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                Raw API keys are secrets. They are encrypted at rest, are never displayed on this page, and are never sent to the
                browser, JavaScript, HTML, API responses or logs.
            </p>
            <p class="mt-1 text-xs text-red-700 dark:text-red-400">
                Any key shows only its last 4 characters. If you suspect a leak, use "Rotate key" on the AI Providers page to
                re-encrypt it with the current application cipher.
            </p>
        </div>

        {{-- Encryption mode --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Encryption at rest</p>
                <p class="mt-1 text-lg font-semibold {{ $encryption['at_rest'] ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $encryption['at_rest'] ? 'Enabled' : 'Disabled' }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">config('ai.encrypted_keys_at_rest')</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Cipher</p>
                <p class="mt-1 font-mono text-lg font-semibold text-gray-900 dark:text-white">{{ $encryption['cipher'] ?? 'unknown' }}</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">config('app.cipher') — used by Laravel Crypto</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">APP_KEY available</p>
                <p class="mt-1 text-lg font-semibold {{ $encryption['app_key_available'] ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $encryption['app_key_available'] ? 'Yes' : 'No' }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Keys cannot be decrypted without it</p>
            </div>
        </div>

        {{-- Providers with masked keys --}}
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Provider Credentials</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Masked keys only — never stored in .env, decrypted server-side per request.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Provider</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Model</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Masked Key</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Activity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Last Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($providers as $provider)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($provider['provider']) }}</span>
                                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-300">{{ $provider['provider'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $provider['model'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $provider['masked_key'] ?? '**** (no key)' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $provider['enabled'] ? 'badge-green' : 'badge-red' }}">
                                        {{ $provider['enabled'] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    {{ number_format($provider['request_count']) }} req · {{ number_format($provider['error_count']) }} err
                                    @if ($provider['last_success_at'])
                                        <br><span class="text-gray-400 dark:text-gray-500">OK {{ \Illuminate\Support\Carbon::parse($provider['last_success_at'])->diffForHumans() }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($provider['last_error'])
                                        <span class="max-w-[220px] inline-block truncate text-xs text-red-600 dark:text-red-400" title="{{ $provider['last_error'] }}">
                                            {{ \Illuminate\Support\Str::limit($provider['last_error'], 50) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No providers configured. <a href="{{ route('admin.ai-providers.setup') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Open the setup wizard</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Usage totals --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Org requests today</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($usage['today']) }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Org requests this month</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($usage['month']) }}</p>
            </div>
        </div>

        {{-- Recent usage log & audit --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent AI Usage</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Latest entries from ai_usage_logs (never stores prompts or keys).</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">When</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Provider</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($recentUsage as $log)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $log->provider }}@if ($log->model) <span class="text-xs text-gray-400">{{ $log->model }}</span>@endif</td>
                                    <td class="px-4 py-2">
                                        <span class="badge {{ $log->status === 'success' ? 'badge-green' : 'badge-red' }}">{{ $log->status }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-300">${{ number_format((float) $log->cost, 4) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400 dark:text-gray-500">No usage recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Provider Audit</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Actions taken on AI provider rows (module ai_providers).</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">When</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Event</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($audits as $audit)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $audit->created_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $audit->event }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-300">{{ $audit->user?->name ?? 'system' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-400 dark:text-gray-500">No AI provider audit entries yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Provider health policy --}}
        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Provider Health Policy</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                The AI router temporarily skips a provider that fails repeatedly within the window (config('ai.health')). A success resets the counter.
            </p>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="rounded bg-gray-100 px-2 py-1 text-gray-700 dark:bg-gray-700 dark:text-gray-200">max failures: <b>{{ $healthPolicy['max_failures'] ?? 'n/a' }}</b></span>
                <span class="rounded bg-gray-100 px-2 py-1 text-gray-700 dark:bg-gray-700 dark:text-gray-200">window: <b>{{ isset($healthPolicy['window_seconds']) ? $healthPolicy['window_seconds'].'s' : 'n/a' }}</b></span>
            </div>
        </div>
    </div>
</x-layouts.app>