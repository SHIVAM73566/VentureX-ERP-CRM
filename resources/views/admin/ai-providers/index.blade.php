<x-layouts.app
    :title="'AI Providers'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'AI Providers']]">

    <div class="mx-auto max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Providers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Connect AI providers once here — API keys are encrypted at rest, masked in the UI, and never stored in plaintext.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.ai-providers.setup') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Setup Wizard
                </a>
                <a href="{{ route('admin.ai-providers.security') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    API Security
                </a>
            </div>
        </div>

        {{-- First-run setup banner (non-blocking, dismissible) --}}
        @if ($showSetupWizard && ! $dismissed)
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-900/20">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-2xl">
                        <h2 class="text-base font-semibold text-blue-900 dark:text-blue-100">Welcome to AI Providers — let's get connected</h2>
                        <p class="mt-1 text-sm text-blue-800 dark:text-blue-300">
                            No AI provider is configured yet and no API keys were found in the environment. The rest of the ERP
                            runs normally without AI; connect a provider from the setup wizard when you are ready.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.ai-providers.setup') }}"
                           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Open Setup Wizard
                        </a>
                        <form method="POST" action="{{ route('admin.ai-providers.setup.dismiss') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 dark:border-blue-700 dark:bg-gray-800 dark:text-blue-200 dark:hover:bg-gray-700">
                                Dismiss
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Providers Configured</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['providers'] }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Enabled</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['enabled'] }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Requests Today (org)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['requests_today']) }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Requests This Month (org)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['requests_month']) }}</p>
            </div>
        </div>

        {{-- Provider cards table --}}
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Provider</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Model / Endpoint</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Limits (per-user / org)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Activity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($cards as $card)
                            @php $p = $card['stored']; @endphp
                            <tr class="align-top hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $card['label'] }}</span>
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-300">{{ $card['provider'] }}</span>
                                    </div>

                                    @if ($p)
                                        <p class="mt-2 font-mono text-xs text-gray-600 dark:text-gray-300" title="Stored &amp; encrypted at rest — only the last 4 characters are shown">
                                            {{ $card['masked'] ?? '****' }}
                                        </p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="badge {{ $p->enabled ? 'badge-green' : 'badge-red' }}">{{ $p->enabled ? 'Enabled' : 'Disabled' }}</span>
                                            @if ($p->fallback_enabled)
                                                <span class="badge badge-amber">Fallback on</span>
                                            @endif
                                            <span class="badge">{{ $p->auth_mode ?? '—' }}</span>
                                            @if ($card['has_env_key'])
                                                <span class="badge" title="Configured via config/ai.php or .env, not the database">env</span>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">
                                            @if ($p->created_at)
                                                Added {{ $p->created_at->diffForHumans() }}
                                            @endif
                                        </p>
                                    @else
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span class="badge badge-red">Not configured</span>
                                            @if ($card['has_env_key'])
                                                <span class="badge badge-amber">Key present in env</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.ai-providers.setup') }}" class="mt-2 inline-block text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                                            Configure in wizard →
                                        </a>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    @if ($p)
                                        <form method="POST" action="{{ route('admin.ai-providers.update', $p->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="provider" value="{{ $p->provider }}">
                                            <div class="space-y-2">
                                                <input type="text" name="model" value="{{ $p->model }}"
                                                       placeholder="model"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                <input type="text" name="base_url" value="{{ $p->base_url }}"
                                                       placeholder="base URL (truncated: {{ $p->base_url ? \Illuminate\Support\Str::limit($p->base_url, 40) : 'none' }})"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                <input type="text" name="path" value="{{ $p->path }}"
                                                       placeholder="path (e.g. /v1/chat/completions)"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                <select name="auth_mode"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">— auth mode —</option>
                                                    <option value="bearer" @selected($p->auth_mode === 'bearer')>Bearer token</option>
                                                    <option value="rapidapi" @selected($p->auth_mode === 'rapidapi')>RapidAPI headers</option>
                                                    <option value="google" @selected($p->auth_mode === 'google')>Google (X-Goog-Api-Key)</option>
                                                </select>

                                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" name="enabled" value="1" @checked($p->enabled) class="rounded border-gray-300">
                                                    Enabled
                                                </label>
                                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" name="fallback_enabled" value="1" @checked($p->fallback_enabled) class="rounded border-gray-300">
                                                    Use as fallback
                                                </label>

                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="number" name="per_user_daily_limit" value="{{ $p->per_user_daily_limit }}"
                                                           placeholder="u/day (default {{ $card['limits']['per_user_daily'] ?? '∞' }})" min="0"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                    <input type="number" name="per_user_monthly_limit" value="{{ $p->per_user_monthly_limit }}"
                                                           placeholder="u/mo (default {{ $card['limits']['per_user_monthly'] ?? '∞' }})" min="0"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                    <input type="number" name="org_daily_limit" value="{{ $p->org_daily_limit }}"
                                                           placeholder="org/day (default {{ $card['limits']['org_daily'] ?? '∞' }})" min="0"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                    <input type="number" name="org_monthly_limit" value="{{ $p->org_monthly_limit }}"
                                                           placeholder="org/mo (default {{ $card['limits']['org_monthly'] ?? '∞' }})" min="0"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                </div>

                                                <details class="text-sm">
                                                    <summary class="cursor-pointer text-xs font-medium text-gray-500 dark:text-gray-400">Replace API key</summary>
                                                    <input type="password" name="api_key" placeholder="New API key (optional — encrypted at rest)"
                                                           class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                </details>

                                                <button type="submit"
                                                        class="w-full rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                                                    Save Provider Settings
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <p class="text-sm text-gray-400 dark:text-gray-500">
                                            Add this provider via the setup wizard to manage its model, endpoint and limits.
                                        </p>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    @if ($p)
                                        <div class="space-y-1 text-xs">
                                            <p class="text-gray-700 dark:text-gray-300">
                                                per-user: {{ $p->per_user_daily_limit ?? '∞' }}/day · {{ $p->per_user_monthly_limit ?? '∞' }}/mo
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-300">
                                                org: {{ $p->org_daily_limit ?? '∞' }}/day · {{ $p->org_monthly_limit ?? '∞' }}/mo
                                            </p>
                                            @if ($card['limits']['fallback'])
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Fallback limits apply</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500">—</p>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    @if ($p)
                                        <div class="space-y-1 text-xs">
                                            <p class="text-gray-700 dark:text-gray-300">Requests: <span class="font-semibold">{{ number_format($p->request_count) }}</span></p>
                                            <p class="text-gray-700 dark:text-gray-300">Errors: <span class="font-semibold {{ $p->error_count > 0 ? 'text-red-600 dark:text-red-400' : '' }}">{{ number_format($p->error_count) }}</span></p>
                                            <p class="text-gray-500 dark:text-gray-400">
                                                @if ($p->last_success_at)
                                                    OK {{ \Illuminate\Support\Carbon::parse($p->last_success_at)->diffForHumans() }}
                                                @else
                                                    no success yet
                                                @endif
                                            </p>
                                            @if ($p->last_error)
                                                <p class="max-w-[180px] truncate text-red-600 dark:text-red-400" title="{{ $p->last_error }}">
                                                    {{ \Illuminate\Support\Str::limit($p->last_error, 60) }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500">—</p>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    @if ($p)
                                        <div class="flex flex-col items-end gap-1.5">
                                            <form method="POST" action="{{ route('admin.ai-providers.test', $p->id) }}">@csrf
                                                <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                                    Test connection
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.ai-providers.rotate', $p->id) }}"
                                                  onsubmit="return confirm('Re-encrypt the stored API key for {{ addslashes($p->provider) }} with the current cipher?');">@csrf
                                                <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                                    Rotate key
                                                </button>
                                            </form>
                                            @if ($p->enabled)
                                                <form method="POST" action="{{ route('admin.ai-providers.disable', $p->id) }}">@csrf
                                                    <button type="submit" class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:border-amber-700 dark:text-amber-300 dark:hover:bg-amber-900/20">
                                                        Disable
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.ai-providers.enable', $p->id) }}">@csrf
                                                    <button type="submit" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                                        Enable
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.ai-providers.destroy', $p->id) }}"
                                                  onsubmit="return confirm('Delete the stored config + encrypted key for {{ addslashes($p->provider) }}?');">@csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/20">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500">—</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No providers configured yet. Open the
                                    <a href="{{ route('admin.ai-providers.setup') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">setup wizard</a>
                                    to connect your first AI provider.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Security note --}}
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-300">
                <span class="font-semibold">Keys are secrets.</span> Only the last 4 characters of a key are ever shown here.
                Keys are encrypted at rest with the application cipher and are never written to logs, export files, or API responses.
            </p>
        </div>
    </div>
</x-layouts.app>