<x-layouts.app title="Opportunity Pipeline" :breadcrumbs="[['label' => 'CRM'], ['label' => 'Pipeline']]">

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Open Opportunities" :value="$totals['opportunities']" icon="document" color="blue" />
        <x-dashboard.stat-card label="Pipeline Value" :value="number_format($totals['pipeline_value'], 2)" icon="wallet" color="violet" />
        <x-dashboard.stat-card label="Weighted Value" :value="number_format($totals['weighted_value'], 2)" icon="calculator" color="amber" />
        <x-dashboard.stat-card label="Won" :value="number_format($totals['won_value'], 2)" icon="badge" color="green" />
    </div>

    <div class="mt-6 flex gap-4 overflow-x-auto pb-4">
        @foreach ($columns as $stage => $column)
            <div class="w-72 shrink-0 rounded-xl bg-ink-50/60 p-3">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-ink-800">{{ $column['label'] }}</h3>
                    <span class="text-xs font-medium text-ink-400">{{ $column['count'] }}</span>
                </div>

                @php $accent = ['lead' => 'bg-slate-400', 'qualified' => 'bg-navy-500', 'proposal' => 'bg-amber-500', 'negotiation' => 'bg-violet-500', 'won' => 'bg-emerald-500', 'lost' => 'bg-red-400']; @endphp
                <div class="mb-3 h-1.5 rounded-full {{ $accent[$stage] ?? 'bg-slate-300' }}">
                    <div class="h-full rounded-full bg-ink-900/20"></div>
                </div>

                <div class="space-y-3">
                    @forelse ($column['items'] as $opportunity)
                        <div class="rounded-lg border border-ink-200 bg-white p-3 shadow-sm">
                            <p class="font-semibold text-ink-800">
                                <a href="{{ route('opportunities.show', $opportunity) }}" class="hover:text-navy-600">{{ $opportunity->name }}</a>
                            </p>
                            <p class="mt-0.5 text-xs text-ink-400">{{ $opportunity->customer?->name ?? 'No customer' }}</p>
                            <p class="mt-2 text-lg font-bold text-ink-900">{{ number_format((float) $opportunity->expected_value, 2) }}</p>
                            <p class="text-xs text-ink-400">{{ $opportunity->probability }}% • {{ $opportunity->assignedTo?->name ?? 'Unassigned' }}</p>
                            <form method="POST" action="{{ route('pipeline.move', $opportunity) }}" class="mt-2">
                                @csrf @method('PATCH')
                                <select name="stage" onchange="this.form.submit()" class="input text-xs py-1.5">
                                    @foreach ($stages as $key => $label)
                                        <option value="{{ $key }}" @selected($key === $stage)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-ink-400">No opportunities.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
