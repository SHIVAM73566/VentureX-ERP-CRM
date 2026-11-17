<x-layouts.app :title="'AI Quotas'" :breadcrumbs="[['label' => 'Administration'], ['label' => 'AI Quotas']]">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-ink-900 dark:text-ink-50">AI Quota Configuration</h1>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Configure daily and weekly AI request limits per role. Changes take effect immediately.</p>
            </div>
            <div class="text-right text-sm">
                <p class="text-ink-500 dark:text-ink-400">Your quota today</p>
                <p class="font-semibold text-ink-900 dark:text-ink-50">{{ $stats['daily_used'] }} / {{ $stats['daily_limit'] }} daily</p>
                <p class="font-semibold text-ink-900 dark:text-ink-50">{{ $stats['weekly_used'] }} / {{ $stats['weekly_limit'] }} weekly</p>
            </div>
        </div>

        @if($stats['alerts'])
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    ⚠️ You have used {{ max($stats['daily_used'] / max($stats['daily_limit'], 1), $stats['weekly_used'] / max($stats['weekly_limit'], 1)) * 100 }}% of your AI quota.
                </p>
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <p class="text-sm text-ink-500 dark:text-ink-400">Total Requests (7d)</p>
                <p class="truncate text-2xl font-bold tabular-nums text-ink-900 dark:text-ink-50" title="{{ number_format($usageStats['requests']) }}">{{ number_format($usageStats['requests']) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-sm text-ink-500 dark:text-ink-400">Cache Hit Rate</p>
                <p class="truncate text-2xl font-bold tabular-nums text-ink-900 dark:text-ink-50">{{ $usageStats['cache_hit_rate'] }}%</p>
            </div>
            <div class="card p-4">
                <p class="text-sm text-ink-500 dark:text-ink-400">API Calls (7d)</p>
                <p class="truncate text-2xl font-bold tabular-nums text-ink-900 dark:text-ink-50" title="{{ number_format($usageStats['api_calls']) }}">{{ number_format($usageStats['api_calls']) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-sm text-ink-500 dark:text-ink-400">Estimated Cost</p>
                <p class="truncate text-2xl font-bold tabular-nums text-ink-900 dark:text-ink-50" title="${{ number_format($usageStats['estimated_cost'], 2) }}">${{ number_format($usageStats['estimated_cost'], 2) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.ai-quotas.update') }}">
            @csrf
            @method('PUT')
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="text-sm font-semibold text-ink-900 dark:text-ink-50">Role Quotas</h2>
                </div>
                <div class="p-5">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 sm:px-6">Role</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 sm:px-6">Daily Limit</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 sm:px-6">Weekly Limit</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 sm:px-6">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-200 dark:divide-ink-800">
                                @foreach($quotas as $role => $quota)
                                    <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                        <td class="px-4 py-4 text-sm font-medium text-ink-900 dark:text-ink-100 whitespace-nowrap sm:px-6">
                                            {{ ucwords(str_replace('_', ' ', $role)) }}
                                        </td>
                                        <td class="px-4 py-4 sm:px-6">
                                            <input type="number" name="quotas[{{ $role }}][daily]"
                                                   value="{{ $quota['daily'] }}"
                                                   min="0" max="9999"
                                                   class="input w-20 tabular-nums sm:w-24">
                                        </td>
                                        <td class="px-4 py-4 sm:px-6">
                                            <input type="number" name="quotas[{{ $role }}][weekly]"
                                                   value="{{ $quota['weekly'] }}"
                                                   min="0" max="9999"
                                                   class="input w-20 tabular-nums sm:w-24">
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm sm:px-6">
                                            @if($quota['overridden'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-navy-100 text-navy-800 dark:bg-navy-900 dark:text-navy-200">
                                                    Custom
                                                </span>
                                            @else
                                                <span class="text-ink-400 dark:text-ink-500 text-xs">Default</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-primary text-sm px-6 py-2">Save Quota Settings</button>
            </div>
        </form>
    </div>
</x-layouts.app>
