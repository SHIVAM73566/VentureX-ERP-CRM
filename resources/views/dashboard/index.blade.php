<x-layouts.app title="Dashboard" :breadcrumbs="[['label' => 'Dashboard']]">

    {{-- First-run onboarding banner --}}
    <div x-data="{ show: localStorage.getItem('vx-getting-started-banner') !== 'dismissed' }"
         x-show="show" x-cloak
         class="mb-5 flex flex-wrap items-center gap-3 rounded-xl border border-navy-200 bg-navy-50 px-4 py-3 dark:border-navy-700 dark:bg-navy-900/40">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-navy-800 text-white">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-navy-900 dark:text-navy-100">New here? Take the 10-minute product tour.</p>
            <p class="text-xs text-navy-700 dark:text-navy-300">See the first 7 steps, copy real examples, and track your onboarding checklist.</p>
        </div>
        <a href="{{ route('getting-started') }}" class="btn-primary">Open Getting Started</a>
        <button type="button" @click="show = false; localStorage.setItem('vx-getting-started-banner', 'dismissed')"
                class="rounded-lg p-1.5 text-navy-600 hover:bg-navy-100 dark:text-navy-300 dark:hover:bg-navy-800" aria-label="Dismiss">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @if(!layer_enabled('updates_support'))
    {{-- Contextual upgrade prompt (max 1 per session, dismissible) --}}
    <div x-data="{ show: sessionStorage.getItem('vx-upgrade-upsell-dismissed') !== '1' }"
         x-show="show" x-cloak
         class="mb-5 flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:bg-amber-900/20">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Unlock updates &amp; support</p>
            <p class="text-xs text-amber-800 dark:text-amber-200">Keep your ERP current with the Updates &amp; Support (Care) layer. See Plan &amp; Layers in Settings.</p>
        </div>
        <a href="{{ route('admin.settings.index', ['tab' => 'plan']) }}" class="btn-secondary text-xs">View plans</a>
        <button type="button" @click="show = false; sessionStorage.setItem('vx-upgrade-upsell-dismissed', '1')"
                class="rounded-lg p-1.5 text-amber-700 hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-900/40" aria-label="Dismiss">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif


    @php
        $user = auth()->user();
        $icons = [
            'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'target' => 'M12 2a10 10 0 100 20 10 10 0 000-20zM12 6a6 6 0 100 12 6 6 0 000-12zm0 4a2 2 0 100 4 2 2 0 000-4z',
            'trending' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'wallet' => 'M3 10h18M7 15h2m-5 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z',
            'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'cart' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
            'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'ship' => 'M3 13l9 5 9-5M3 13l9-5 9 5M3 13v5l9 5 9-5v-5M12 3v5m0 0l2 1.5M12 8l-2 1.5',
            'folder' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
            'spark' => 'M5 3l.8 2.2L8 6l-2.2.8L5 9l-.8-2.2L2 6l2.2-.8L5 3zm11 0l.8 2.2L19 6l-2.2.8L16 9l-.8-2.2L13 6l2.2-.8L16 3zm-5 5l.8 2.2L14 11l-2.2.8L11 14l-.8-2.2L8 11l2.2-.8L11 8zm6 7l.8 2.2L20 18l-2.2.8L17 21l-.8-2.2L14 18l2.2-.8L17 15z',
            'scan' => 'M4 7V4h3m10 0h3v3M4 17v3h3m10 0h3v-3m-3-7a4 4 0 00-4-4 4 4 0 00-4 4 4 4 0 00-4 4 4 4 0 004 4 4 4 0 004-4 4 4 0 004-4z',
            'bolt' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'briefcase' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'badge' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 112 0v1m-2 1a2 2 0 002 2h2a2 2 0 002-2m-6 1v5m0 0l-2-2m2 2l2-2',
        ];
    @endphp

    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 300)">
        {{-- Skeleton state --}}
        <div x-show="loading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            @for($i = 0; $i < 4; $i++)
                <x-ui.skeleton-stat />
            @endfor
        </div>

        {{-- Real content --}}
        <div x-show="!loading" x-cloak>
    @if(!empty($quickActions))
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach($quickActions as $action)
                <a href="{{ route($action['route']) }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition
                    {{ $action['color'] === 'blue' ? 'bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20' : '' }}
                    {{ $action['color'] === 'violet' ? 'bg-violet-50 text-violet-700 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20' : '' }}
                    {{ $action['color'] === 'amber' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20' : '' }}
                    {{ $action['color'] === 'green' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20' : '' }}
                    {{ $action['color'] === 'gray' ? 'bg-gray-50 text-gray-700 hover:bg-gray-100 dark:bg-gray-500/10 dark:text-gray-400 dark:hover:bg-gray-500/20' : '' }}
                    {{ $action['color'] === 'purple' ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-500/10 dark:text-purple-400 dark:hover:bg-purple-500/20' : '' }}">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @if($user->hasAnyPermission(['customers.view', 'leads.view']) || $isExecutive)
                    <x-dashboard.stat-card label="Customers" :value="$stats['customers']" icon="users" color="blue" :url="route('customers.index')" />
                @endif
                @if($user->hasPermissionTo('leads.view') || $isExecutive)
                    <x-dashboard.stat-card label="Leads" :value="$stats['leads']" icon="target" color="violet" :url="route('leads.index')" :sub="'New: '.$stats['leads_new']" />
                @endif
                @if($user->hasPermissionTo('leads.view') || $isExecutive)
                    <x-dashboard.stat-card label="Open Opportunities" :value="$stats['open_opportunities']" icon="trending" color="amber" :url="route('opportunities.index')" />
                @endif
                @if($user->hasPermissionTo('finance.view') || $isExecutive)
                    <x-dashboard.stat-card label="Pipeline Value" :value="$currencySymbol.number_format($stats['pipeline_value'])" icon="wallet" color="green" :sub="'Won: '.$currencySymbol.number_format($stats['won_value'])" />
                @endif
            </div>
        </div>
    </div>

    {{-- Executive briefing --}}
    @if (! empty($briefing))
        @php
            $toneMap = [
                'blue' => 'bg-navy-500',
                'green' => 'bg-emerald-500',
                'red' => 'bg-red-500',
                'amber' => 'bg-amber-500',
                'violet' => 'bg-violet-500',
            ];
        @endphp
        <div class="mt-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Executive briefing</h2>
                    <span class="text-xs text-ink-400 dark:text-ink-500">{{ now()->format('l, d M Y') }}</span>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($briefing as $item)
                        <div class="flex items-start gap-3 rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $toneMap[$item['tone']] ?? 'bg-navy-500' }}"></span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">{{ $item['label'] }}</p>
                                <p class="text-sm font-medium text-ink-800 dark:text-ink-100">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if (($user->hasPermissionTo('finance.view') || $isExecutive) && ! empty($cashFlow['months']))
        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Predictive cash-flow forecast</h2>
                    <span class="text-xs text-ink-400 dark:text-ink-500">Next 6 months · receivables vs purchase commitments</span>
                </div>
                <div class="p-5">
                    @php
                        $peak = max(1, collect($cashFlow['months'])->flatMap(fn ($m) => [$m['inflow'], $m['outflow']])->max());
                    @endphp
                    <div class="flex h-56 items-end justify-between gap-3">
                        @foreach ($cashFlow['months'] as $month)
                            <div class="flex flex-1 flex-col items-center justify-end gap-2">
                                <div class="flex w-full items-end justify-center gap-1" style="height: 10rem;">
                                    <div class="w-1/2 rounded-t bg-emerald-400/80 transition-all dark:bg-emerald-500/70" style="height: {{ max(2, round($month['inflow'] / $peak * 100)) }}%" title="Expected inflow {{ number_format($month['inflow'], 0) }}"></div>
                                    <div class="w-1/2 rounded-t bg-red-400/80 transition-all dark:bg-red-500/70" style="height: {{ max(2, round($month['outflow'] / $peak * 100)) }}%" title="Purchase commitments {{ number_format($month['outflow'], 0) }}"></div>
                                </div>
                                <span class="text-xs font-semibold text-ink-600 dark:text-ink-300">{{ $month['label'] }}</span>
                                <span class="text-[10px] font-semibold {{ $month['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $month['net'] >= 0 ? '+' : '-' }}{{ number_format(abs($month['net']), 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-ink-500 dark:text-ink-400">
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span> Expected inflow</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-red-400"></span> Purchase commitments</span>
                        <span class="ml-auto font-semibold {{ $cashFlow['overdue_total'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $cashFlow['overdue_count'] }} overdue · {{ number_format((float) $cashFlow['overdue_total'], 0) }} outstanding
                        </span>
                    </div>
                </div>
            </div>

            @if ($user->hasPermissionTo('hr.view') || $isExecutive)
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Workforce snapshot</h2>
                        <a href="{{ route('hr.dashboard') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">Open HR</a>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-5">
                        <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                            <p class="text-2xl font-bold tabular-nums text-ink-900 dark:text-ink-50">{{ $hrSummary['headcount'] }}</p>
                            <p class="text-xs text-ink-400">Active headcount</p>
                        </div>
                        <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                            <p class="text-2xl font-bold tabular-nums text-amber-600 dark:text-amber-400">{{ $hrSummary['pending_leave'] }}</p>
                            <p class="text-xs text-ink-400">Leave pending</p>
                        </div>
                        <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                            <p class="text-2xl font-bold tabular-nums text-violet-600 dark:text-violet-400">{{ $hrSummary['onboarding_open'] }}</p>
                            <p class="text-xs text-ink-400">Onboarding open</p>
                        </div>
                        <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                            <p class="text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $hrSummary['on_leave'] }}</p>
                            <p class="text-xs text-ink-400">On leave</p>
                        </div>
                        @if ($hrSummary['latest_payroll_status'])
                            <div class="col-span-2 rounded-lg bg-navy-50 p-3 text-xs text-navy-700 dark:bg-navy-500/10 dark:text-navy-300">
                                Latest payroll {{ $hrSummary['latest_payroll_period'] }}: {{ number_format((float) $hrSummary['latest_payroll_net'], 2) }} net ·
                                <span class="font-semibold capitalize">{{ $hrSummary['latest_payroll_status'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($user->hasRole('super_admin'))
        <div class="mt-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">AERO X Agentic Engine</h2>
                    <a href="{{ route('admin.agentic.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">Open control surface</a>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                        <p class="text-2xl font-bold tabular-nums text-ink-900 dark:text-ink-50">{{ $agenticSummary['enabled'] }}<span class="text-sm text-ink-400">/{{ $agenticSummary['rules'] }}</span></p>
                        <p class="text-xs text-ink-400">Rules enabled</p>
                    </div>
                    <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                        <p class="text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $agenticSummary['runs_today'] }}</p>
                        <p class="text-xs text-ink-400">Runs today</p>
                    </div>
                    <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                        <p class="text-2xl font-bold tabular-nums {{ $agenticSummary['rollbacks'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-ink-900 dark:text-ink-50' }}">{{ $agenticSummary['rollbacks'] }}</p>
                        <p class="text-xs text-ink-400">Rollbacks (7d)</p>
                    </div>
                    <div class="rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                        <p class="text-2xl font-bold tabular-nums text-violet-600 dark:text-violet-400">{{ number_format((float) $agenticSummary['success_rate'], 1) }}%</p>
                        <p class="text-xs text-ink-400">Success rate (7d)</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isExecutive && $aiQuota)
        <div class="mt-6">
            <div class="card">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-500/15">
                            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['spark'] }}"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">AI Usage</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500">Your quota: {{ $aiQuota['daily_used'] }}/{{ $aiQuota['daily_limit'] }} daily, {{ $aiQuota['weekly_used'] }}/{{ $aiQuota['weekly_limit'] }} weekly</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($aiQuota['alerts'])
                            @foreach($aiQuota['alerts'] as $alert)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $alert >= 90 ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : ($alert >= 75 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-ink-700 dark:text-ink-300') }}">
                                    {{ $alert }}% used
                                </span>
                            @endforeach
                        @else
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Healthy</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-6">
        <div class="card" x-data="aiInsights()">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">AI Business Insights</h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-ink-400 dark:text-ink-500" x-show="status" x-text="status"></span>
                    <button type="button" @click="generate()" :disabled="busy" x-show="{{ $aiEnabled ? 'true' : 'false' }}"
                        class="btn btn-sm" :class="busy ? 'opacity-50' : ''">
                        <span x-show="!busy">Generate AI insights</span>
                        <span x-show="busy">Generating…</span>
                    </button>
                </div>
            </div>

            <div class="p-5">
                <div id="ai-rule-insights" class="space-y-2">
                    @forelse ($ruleInsights as $insight)
                        @if ($insight['url'])
                            <a href="{{ $insight['url'] }}" class="flex items-start gap-3 rounded-lg border p-3 transition {{ $insight['tone'] === 'critical' ? 'border-red-200 bg-red-50/60 hover:bg-red-50 dark:border-red-800/50 dark:bg-red-500/10 dark:hover:bg-red-500/15' : ($insight['tone'] === 'high' ? 'border-amber-200 bg-amber-50/60 hover:bg-amber-50 dark:border-amber-800/50 dark:bg-amber-500/10 dark:hover:bg-amber-500/15' : 'border-ink-100 hover:bg-ink-50 dark:border-ink-800 dark:hover:bg-ink-800/50') }}">
                        @else
                            <div class="flex items-start gap-3 rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                        @endif
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $insight['tone'] === 'critical' ? 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400' : ($insight['tone'] === 'high' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-navy-100 text-navy-600 dark:bg-navy-500/15 dark:text-navy-400') }} text-xs font-bold">!</span>
                            <p class="text-sm text-ink-700 dark:text-ink-300">{{ $insight['text'] }}</p>
                        @if ($insight['url'])
                            </a>
                        @else
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-ink-400 dark:text-ink-500">No urgent signals right now.</p>
                    @endforelse
                </div>

                <div id="ai-generated" class="mt-4 space-y-2" x-show="content" x-cloak>
                    <div class="rounded-lg border border-navy-100 bg-navy-50/50 p-4 dark:border-navy-800/50 dark:bg-navy-500/10">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-navy-600 dark:text-navy-400">AI summary
                            <span class="ml-1 font-normal normal-case text-ink-400 dark:text-ink-500" x-text="meta"></span>
                        </p>
                        <p class="whitespace-pre-wrap text-sm text-ink-700 dark:text-ink-300" x-text="content"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Analytics & Charts --}}
    @if ($isExecutive || $user->hasPermissionTo('finance.view') || $user->hasPermissionTo('ai.view'))
    <div class="mt-6" x-data="layoutCustomize">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Advanced Analytics</h2>
                    <span class="text-xs text-ink-400 dark:text-ink-500">Real-time charts · Revenue velocity · Pipeline · Inventory · AI quota</span>
                </div>
                <button type="button" @click="$dispatch('open-layout-customizer')"
                        class="btn btn-sm text-xs">Customize layout</button>
            </div>
            <div class="p-5">
                {{-- Row 1: Revenue Velocity + Cash Flow --}}
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="card p-5 xl:col-span-2" x-show="visible('revenue')">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Revenue Velocity</h3>
                            <span class="text-xs text-ink-400 dark:text-ink-500">Invoiced revenue, last 6 months</span>
                        </div>
                        <div class="h-48">
                            <canvas id="chartRevenue"></canvas>
                        </div>
                    </div>
                    <div class="card p-5 xl:col-span-2" x-show="visible('cashflow')">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Cash Flow Projection</h3>
                            <span class="text-xs text-ink-400 dark:text-ink-500">Inflow vs outflow, next 6 months</span>
                        </div>
                        <div class="h-48">
                            <canvas id="chartCashflow"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Pipeline Conversion + Inventory Turnover --}}
                <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="card p-5 xl:col-span-2" x-show="visible('pipeline')">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Pipeline Conversion</h3>
                            <span class="text-xs text-ink-400 dark:text-ink-500">Won vs open vs lost value</span>
                        </div>
                        <div class="flex items-center justify-center h-48">
                            <canvas id="chartPipeline" class="max-w-md max-h-[240px]"></canvas>
                        </div>
                    </div>
                    <div class="card p-5 xl:col-span-2" x-show="visible('inventory')">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">Inventory Turnover</h3>
                            <span class="text-xs text-ink-400 dark:text-ink-500">Stock movement velocity (30 days)</span>
                        </div>
                        <div class="h-48">
                            <canvas id="chartInventory"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Row 3: AI Quota Utilization --}}
                <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="card p-5 xl:col-span-4" x-show="visible('aiquota')">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">AI Request Quota Utilization</h3>
                            <span class="text-xs text-ink-400 dark:text-ink-500">Daily vs weekly quota usage</span>
                        </div>
                        <div class="h-40">
                            <canvas id="chartAiQuota"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($user->hasPermissionTo('leads.view') || $isExecutive)
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Pipeline by Stage</h2>
                <span class="text-xs text-ink-400 dark:text-ink-500">Opportunities</span>
            </div>
            <div class="p-5">
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-ink-100 dark:bg-ink-800" x-data="{}">
                    @php
                        $total = max(1, array_sum($opportunitiesByStage));
                        $colors = ['qualification' => 'bg-ink-400', 'needs_analysis' => 'bg-navy-400', 'proposal' => 'bg-navy-600', 'negotiation' => 'bg-amber-400', 'won' => 'bg-emerald-500', 'lost' => 'bg-red-400'];
                        $labels = \App\Models\Opportunity::STAGES;
                    @endphp
                    @foreach ($opportunitiesByStage as $stage => $count)
                        <div class="{{ $colors[$stage] ?? 'bg-ink-300' }} h-3" style="width: {{ $count / $total * 100 }}%" title="{{ ($labels[$stage] ?? $stage) }}: {{ $count }}"></div>
                    @endforeach
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($opportunitiesByStage as $stage => $count)
                        <div class="flex items-center gap-2 text-xs text-ink-600 dark:text-ink-400">
                            <span class="h-2.5 w-2.5 rounded-full {{ $colors[$stage] ?? 'bg-ink-300' }}"></span>
                            {{ $labels[$stage] ?? $stage }} — {{ $count }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Follow-ups due soon</h2>
                <a href="{{ route('leads.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View all</a>
            </div>
            <div class="divide-y divide-ink-100 dark:divide-ink-800">
                @forelse ($followUps as $lead)
                    @php
                        // Tolerate legacy/denormalized aggregate entries so a
                        // stale cache value can never break the dashboard.
                        $leadName = is_object($lead) ? (string) ($lead->contact_name ?? '') : (string) $lead;
                        $leadCompany = is_object($lead) ? (string) ($lead->company_name ?? '') : '';
                        $leadDue = is_object($lead) ? $lead->next_follow_up : null;
                        $leadDue = ($leadDue && ! is_string($leadDue)) ? $leadDue : null;
                        $leadInitial = $leadName !== '' ? mb_substr($leadName, 0, 1) : '?';
                    @endphp
                    <a href="{{ is_object($lead) ? route('leads.show', $lead) : route('leads.index') }}" class="flex items-start gap-3 p-4 hover:bg-ink-50 dark:hover:bg-ink-800/50">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-xs font-bold text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">{{ $leadInitial }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-800 dark:text-ink-100">{{ $leadName !== '' ? $leadName : 'Unknown contact' }}</p>
                            <p class="truncate text-xs text-ink-500 dark:text-ink-400">{{ $leadCompany }}</p>
                            @if ($leadDue)
                                <p class="mt-1 text-xs font-semibold {{ $leadDue->isPast() ? 'text-red-600 dark:text-red-400' : 'text-ink-400 dark:text-ink-500' }}">Due {{ $leadDue->diffForHumans() }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="p-4 text-sm text-ink-400 dark:text-ink-500">No follow-ups scheduled.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($user->hasAnyPermission(['supplier_offers.view', 'suppliers.view']) || $isExecutive)
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Recent Supplier Offers</h2>
                @if ($navigationAccess->can('supplier-offers.index'))
                    <a href="{{ route('supplier-offers.index') }}" class="text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">All offers</a>
                @endif
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Material</th>
                            <th>Qty (MT)</th>
                            <th>Price/MT</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOffers as $offer)
                            <tr>
                                <td class="font-medium text-ink-800 dark:text-ink-100">{{ $offer->supplier?->name ?? 'Unknown' }}</td>
                                <td>{{ $offer->material_description }}</td>
                                <td>{{ number_format((float) $offer->quantity_mt, 2) }}</td>
                                <td>{{ $offer->currency_code }} {{ number_format((float) $offer->price_per_mt, 2) }}</td>
                                <td>
                                    <span class="{{ $offer->quality_status === 'GREEN' ? 'badge-green' : ($offer->quality_status === 'RED' ? 'badge-red' : 'badge-yellow') }}">{{ $offer->quality_status ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-ink-400 dark:text-ink-500">No offers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Procurement Risk Overview</h2>
                <span class="text-xs text-ink-400 dark:text-ink-500">{{ $offers['total'] }} offers analysed</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-center dark:border-emerald-800/50 dark:bg-emerald-500/10">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $offers['green'] }}</p>
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">GREEN</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-center dark:border-amber-800/50 dark:bg-amber-500/10">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $offers['yellow'] }}</p>
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">YELLOW</p>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-center dark:border-red-800/50 dark:bg-red-500/10">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $offers['red'] }}</p>
                    <p class="text-xs font-semibold text-red-700 dark:text-red-400">RED</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="mt-6">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Module Rollout</h2>
                <span class="text-xs text-ink-400 dark:text-ink-500">Incremental delivery plan</span>
            </div>
            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($modules as $module)
                    @if ($module['route'])
                        <a href="{{ route($module['route']) }}" class="flex items-center gap-3 rounded-lg border p-3 transition {{ $module['ready'] ? 'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 dark:border-emerald-800/50 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/15' : 'border-ink-200 bg-white hover:bg-ink-50 dark:border-ink-800 dark:bg-ink-800/50 dark:hover:bg-ink-800' }}">
                    @else
                        <div class="flex items-center gap-3 rounded-lg border p-3 {{ $module['ready'] ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-800/50 dark:bg-emerald-500/10' : 'border-ink-200 bg-white dark:border-ink-800 dark:bg-ink-800/50' }}">
                    @endif
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $module['ready'] ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-ink-100 text-ink-400 dark:bg-ink-800 dark:text-ink-500' }}">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$module['icon']] ?? '' }}"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $module['name'] }}</p>
                            <p class="text-xs {{ $module['ready'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-ink-400 dark:text-ink-500' }}">{{ $module['ready'] ? 'Live' : 'Planned' }}</p>
                        </div>
                    @if ($module['route'])
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script>
        function aiInsights() {
            return {
                busy: false,
                status: '',
                content: '',
                meta: '',
                init() {
                    const existing = @json($aiInsights ? ['content' => $aiInsights['content'], 'generated_at' => $aiInsights['generated_at'], 'provider' => $aiInsights['provider']] : null);
                    if (existing) {
                        this.content = existing.content;
                        this.meta = 'Generated ' + new Date(existing.generated_at).toLocaleString() + ' · ' + existing.provider;
                    }
                },
                async generate() {
                    if (this.busy) return;
                    this.busy = true;
                    this.status = 'Analysing your data…';
                    try {
                        const res = await fetch(@json(route('ai.insights.generate')), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            this.content = data.content;
                            this.meta = 'Generated ' + new Date(data.generated_at).toLocaleString() + ' · ' + data.provider;
                            this.status = 'Updated';
                        } else {
                            this.status = '';
                            alert(data.error || 'AI is not configured yet. Set RAPIDAPI_KEY to enable AI insights.');
                        }
                    } catch (err) {
                        this.status = '';
                        alert('Could not reach the server. Please try again.');
                    } finally {
                        this.busy = false;
                    }
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('layoutCustomize', () => ({
                open: false,
                widgets: {!! json_encode([
                    ['revenue', 'Revenue Velocity', true],
                    ['cashflow', 'Cash Flow Projection', true],
                    ['pipeline', 'Pipeline Conversion', true],
                    ['inventory', 'Inventory Turnover', true],
                    ['aiquota', 'AI Quota Utilization', true],
                    ['briefing', 'Executive Briefing', true],
                    ['aiInsights', 'AI Business Insights', true],
                ]) !!},
                theme: 'system',
                init() {
                    const saved = localStorage.getItem('vx-layout-widgets');
                    if (saved) {
                        try {
                            const parsed = JSON.parse(saved);
                            this.widgets = this.widgets.map(w => [w[0], w[1], parsed[w[0]] !== false]);
                        } catch (e) {}
                    }
                    const theme = localStorage.getItem('vx-theme') || 'system';
                    this.theme = theme;
                    this.applyTheme();
                },
                toggle(widget) {
                    const found = this.widgets.find(w => w[0] === widget);
                    if (found) found[2] = !found[2];
                    localStorage.setItem('vx-layout-widgets', Object.fromEntries(this.widgets.map(w => [w[0], w[2]])));
                },
                visible(widget) {
                    const saved = localStorage.getItem('vx-layout-widgets');
                    if (saved) {
                        try { return JSON.parse(saved)[widget] !== false; } catch (e) {}
                    }
                    return true;
                },
                applyTheme() {
                    const dark = this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                },
                save() {
                    localStorage.setItem('vx-theme', this.theme);
                    this.applyTheme();
                    this.open = false;
                },
            }));
        });

        window.addEventListener('DOMContentLoaded', () => {
            const renderCharts = () => {
                if (typeof Chart === 'undefined') return;

                Chart.defaults.color = '#64748b';
                Chart.defaults.font.family = '"Instrument Sans", ui-sans-serif, system-ui, sans-serif';
                Chart.defaults.font.size = 11;

                const revCtx = document.getElementById('chartRevenue');
                if (revCtx) {
                    new Chart(revCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: @json($analytics['revenueVelocity']['labels'] ?? []),
                            datasets: [{
                                label: 'Revenue',
                                data: @json($analytics['revenueVelocity']['data'] ?? []),
                                borderColor: '#4372ae',
                                backgroundColor: 'rgba(67, 114, 174, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { grid: { color: '#e2e8f0' }, ticks: { callback: v => v >= 1000 ? (v/1000).toFixed(1)+'K' : v } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                const cfCtx = document.getElementById('chartCashflow');
                if (cfCtx) {
                    new Chart(cfCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: @json(collect($cashFlow['months'] ?? [])->pluck('label')->toArray()),
                            datasets: [{
                                label: 'Inflow',
                                data: @json(collect($cashFlow['months'] ?? [])->pluck('inflow')->toArray()),
                                backgroundColor: '#20a67d',
                            }, {
                                label: 'Outflow',
                                data: @json(collect($cashFlow['months'] ?? [])->pluck('outflow')->toArray()),
                                backgroundColor: '#ef4444',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: { grid: { color: '#e2e8f0' }, stacked: false, ticks: { callback: v => v >= 1000 ? (v/1000).toFixed(1)+'K' : v } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                const pipeCtx = document.getElementById('chartPipeline');
                if (pipeCtx) {
                    new Chart(pipeCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Won', 'Open', 'Lost'],
                            datasets: [{
                                data: [
                                    @json($analytics['pipelineConversion']['won'] ?? 0),
                                    @json($analytics['pipelineConversion']['open'] ?? 0),
                                    @json($analytics['pipelineConversion']['lost'] ?? 0),
                                ],
                                backgroundColor: ['#20a67d', '#4372ae', '#ef4444'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(ctx.parsed) } }
                            }
                        }
                    });
                }

                const invCtx = document.getElementById('chartInventory');
                if (invCtx) {
                    new Chart(invCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Stock In', 'Stock Out'],
                            datasets: [{
                                label: 'Units (30 days)',
                                data: [@json($analytics['inventoryTurnover']['stock_in'] ?? 0), @json($analytics['inventoryTurnover']['stock_out'] ?? 0)],
                                backgroundColor: ['#20a67d', '#4372ae'],
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { grid: { color: '#e2e8f0' }, beginAtZero: true } }
                        }
                    });
                }

                const aiCtx = document.getElementById('chartAiQuota');
                if (aiCtx) {
                    new Chart(aiCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Daily', 'Weekly'],
                            datasets: [{
                                label: 'Used',
                                data: [@json($analytics['aiQuotaTrend']['daily_used'] ?? 0), @json($analytics['aiQuotaTrend']['weekly_used'] ?? 0)],
                                backgroundColor: '#4372ae',
                            }, {
                                label: 'Limit',
                                data: [@json($analytics['aiQuotaTrend']['daily_limit'] ?? 0), @json($analytics['aiQuotaTrend']['weekly_limit'] ?? 0)],
                                backgroundColor: '#cbd5e1',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
                            scales: { y: { grid: { color: '#e2e8f0' }, beginAtZero: true } }
                        }
                    });
                }
            };

            renderCharts();
            setTimeout(renderCharts, 300);
        });
    </script>
    @endpush

    {{-- Layout Customization Drawer --}}
    <div x-data="layoutCustomize" x-show="open" x-cloak data-overlay
         @open-layout-customizer.window="open = true"
         @click.away="open = false"
         class="fixed inset-0 z-50">
        <div x-show="open" x-transition.opacity @click="open = false" data-close-overlay class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm"></div>
        <aside x-show="open"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full opacity-0"
               x-transition:enter-end="translate-x-0 opacity-100"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0 opacity-100"
               x-transition:leave-end="translate-x-full opacity-0"
               class="absolute inset-y-0 right-0 w-full max-w-md flex-col bg-white shadow-2xl dark:bg-ink-900 z-50">
            <div class="border-b border-ink-200 px-6 py-5 dark:border-ink-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-ink-900 dark:text-ink-100">Customize Layout</h3>
                <button type="button" @click="open = false" data-close-overlay
                        class="rounded-lg p-2 text-ink-400 hover:bg-ink-100 dark:hover:bg-ink-800 dark:text-ink-400"
                        aria-label="Close layout customizer">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-ink-400 dark:text-ink-500 mb-3">Theme</h4>
                    <div class="flex gap-2">
                        <button @click="theme = 'system'; save()" :class="theme === 'system' ? 'bg-navy-800 text-white' : 'bg-ink-100 text-ink-600 border border-ink-200'" class="flex-1 py-2 rounded-lg text-xs font-semibold transition dark:border-ink-800">System</button>
                        <button @click="theme = 'light'; save()" :class="theme === 'light' ? 'bg-navy-800 text-white' : 'bg-ink-100 text-ink-600 border border-ink-200'" class="flex-1 py-2 rounded-lg text-xs font-semibold transition dark:border-ink-800">Light</button>
                        <button @click="theme = 'dark'; save()" :class="theme === 'dark' ? 'bg-navy-800 text-white' : 'bg-ink-100 text-ink-600 border border-ink-200'" class="flex-1 py-2 rounded-lg text-xs font-semibold transition dark:border-ink-800">Dark</button>
                    </div>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-ink-400 dark:text-ink-500 mb-3">Dashboard Widgets</h4>
                    <div class="space-y-2">
                        <template x-for="widget in widgets" :key="widget[0]">
                            <div class="flex items-center justify-between p-3 rounded-lg border border-ink-100 hover:border-navy-300/50 dark:border-ink-800 dark:hover:border-navy-500/50">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" x-model="widget[2]" @change="toggle(widget[0])"
                                            class="rounded text-navy-600 focus:ring-navy-500 dark:text-navy-400">
                                    <span class="text-sm text-ink-700 dark:text-ink-300" x-text="widget[1]"></span>
                                </div>
                                <span x-show="widget[2]" class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Visible</span>
                                <span x-show="!widget[2]" class="text-[10px] font-semibold text-ink-400 dark:text-ink-500">Hidden</span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="pt-4 border-t border-ink-100 dark:border-ink-800">
                    <p class="text-xs text-ink-500 dark:text-ink-400">Widget visibility and theme are saved to your browser and apply across sessions.</p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.app>
