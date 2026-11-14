<x-layouts.app title="Automation" :breadcrumbs="[['label' => 'Admin'], ['label' => 'AERO X Agentic Engine']]">

    <x-slot name="actions">
        @can('create', App\Models\AgentAction::class)
            <form method="POST" action="{{ route('admin.agentic.run') }}">
                @csrf
                <input type="hidden" name="dry_run" value="1">
                <button class="btn-secondary">Dry Run All</button>
            </form>
            <form method="POST" action="{{ route('admin.agentic.run') }}">
                @csrf
                <button class="btn-accent">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Run Engine Now
                </button>
            </form>
        @endcan
    </x-slot>

    <div class="rounded-xl border border-navy-200 bg-navy-50 p-4 text-sm text-navy-800 dark:border-navy-700 dark:bg-navy-900/40 dark:text-navy-200">
        <p class="font-semibold">Autonomous execution with rollback protection</p>
        <p class="mt-1 text-xs text-navy-600 dark:text-navy-300">
            Every rule runs inside a database transaction. A failure rolls back cleanly, decrements the rule's confidence,
            and is recorded in the run ledger with the captured exception. The scheduler evaluates due rules every 15 minutes.
        </p>
    </div>

    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Active Rules" :value="$summary['enabled'].' / '.$summary['rules']" icon="spark" color="blue" />
        <x-dashboard.stat-card label="Runs Today" :value="$summary['runs_today']" icon="clock" color="green" />
        <x-dashboard.stat-card label="Avg. Confidence" :value="number_format($summary['success_rate'], 1).'%'" icon="chart" color="violet" />
        <x-dashboard.stat-card label="Rollbacks" :value="$summary['rollbacks']" icon="alert" color="red" />
    </div>

    <div class="mt-6 space-y-4">
        @foreach ($actions as $action)
            <div class="card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-bold text-ink-900 dark:text-ink-50">{{ $action->name }}</h3>
                            <span class="badge-{{ $action->is_enabled ? 'green' : 'gray' }}">{{ $action->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                            <span class="badge-violet">{{ \App\Models\AgentAction::CATEGORIES[$action->category] ?? $action->category }}</span>
                            @if ($action->requires_approval)
                                <span class="badge-amber">Needs approval</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ $action->description }}</p>
                        <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-ink-400">
                            <div><dt class="inline">Runs:</dt> <dd class="inline font-medium text-ink-600 dark:text-ink-300">{{ $action->run_count }}</dd></div>
                            <div><dt class="inline">Success:</dt> <dd class="inline font-medium text-emerald-600">{{ $action->success_count }}</dd></div>
                            <div><dt class="inline">Failures:</dt> <dd class="inline font-medium {{ $action->failure_count > 0 ? 'text-red-600' : 'text-ink-600' }}">{{ $action->failure_count }}</dd></div>
                            <div><dt class="inline">Confidence:</dt> <dd class="inline font-medium text-ink-600 dark:text-ink-300">{{ number_format((float) $action->confidence * 100, 1) }}%</dd></div>
                            <div><dt class="inline">Cooldown:</dt> <dd class="inline font-medium text-ink-600 dark:text-ink-300">{{ $action->cooldown_minutes }} min</dd></div>
                            <div><dt class="inline">Last run:</dt> <dd class="inline font-medium text-ink-600 dark:text-ink-300">{{ $action->last_run_at?->diffForHumans() ?? 'Never' }}</dd></div>
                        </dl>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-2">
                        @can('create', App\Models\AgentAction::class)
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.agentic.run-one', $action) }}">
                                    @csrf
                                    <input type="hidden" name="dry_run" value="1">
                                    <button class="btn-secondary !py-1.5 !text-xs">Dry Run</button>
                                </form>
                                <form method="POST" action="{{ route('admin.agentic.run-one', $action) }}">
                                    @csrf
                                    <button class="btn-accent !py-1.5 !text-xs">Run</button>
                                </form>
                            </div>
                        @endcan

                        @can('update', $action)
                            <details class="w-72">
                                <summary class="cursor-pointer text-xs font-medium text-navy-600 hover:text-navy-500">Configure</summary>
                                <form method="POST" action="{{ route('admin.agentic.update', $action) }}" class="mt-3 space-y-3 rounded-lg border border-ink-200 p-3 dark:border-ink-700">
                                    @csrf @method('PUT')
                                    <label class="flex items-center gap-2 text-xs text-ink-600 dark:text-ink-300">
                                        <input type="hidden" name="is_enabled" value="0">
                                        <input type="checkbox" name="is_enabled" value="1" @checked($action->is_enabled) class="h-4 w-4 rounded border-ink-300 text-accent-600">
                                        Enabled
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-ink-600 dark:text-ink-300">
                                        <input type="hidden" name="requires_approval" value="0">
                                        <input type="checkbox" name="requires_approval" value="1" @checked($action->requires_approval) class="h-4 w-4 rounded border-ink-300 text-accent-600">
                                        Require human approval
                                    </label>
                                    <div>
                                        <label class="label !text-xs">Cooldown (minutes)</label>
                                        <input type="number" name="cooldown_minutes" min="5" max="10080" value="{{ $action->cooldown_minutes }}" class="input !py-1.5 !text-xs">
                                    </div>
                                    <div>
                                        <label class="label !text-xs">Confidence (0–1)</label>
                                        <input type="number" name="confidence" step="0.01" min="0" max="1" value="{{ $action->confidence }}" class="input !py-1.5 !text-xs">
                                    </div>
                                    <button type="submit" class="btn-primary w-full !py-1.5 !text-xs">Save Rule</button>
                                </form>
                            </details>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mt-6">
        <div class="card-header">
            <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Execution ledger</h2>
            <span class="text-xs text-ink-400">Latest 25 runs</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Rule</th><th>Status</th><th>Summary</th><th>Duration</th><th>Trigger</th><th>When</th></tr></thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr>
                            <td class="font-mono text-xs">{{ $run->action_key }}</td>
                            <td>
                                <span class="badge-{{ $run->status === 'success' ? 'green' : ($run->status === 'skipped' ? 'gray' : ($run->status === 'rolled_back' || $run->status === 'failed' ? 'red' : 'blue')) }}">
                                    {{ \App\Models\AgentRun::STATUSES[$run->status] ?? $run->status }}
                                </span>
                                @if ($run->dry_run)
                                    <span class="badge-violet">dry run</span>
                                @endif
                            </td>
                            <td class="max-w-[28rem] truncate text-ink-600 dark:text-ink-300" title="{{ $run->summary }}">{{ $run->summary ?? '—' }}</td>
                            <td class="tabular-nums">{{ $run->duration_ms !== null ? number_format((float) $run->duration_ms, 0).' ms' : '—' }}</td>
                            <td class="text-xs text-ink-400">{{ str_replace('_', ' ', $run->trigger) }}</td>
                            <td class="text-xs text-ink-400">{{ $run->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-ink-400">No agent runs recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
