<x-layouts.app title="{{ $opportunity->name }}" :breadcrumbs="[['label' => 'CRM', 'url' => route('opportunities.index')], ['label' => 'Opportunities', 'url' => route('opportunities.index')], ['label' => $opportunity->name]]">

    <x-slot name="actions">
        @can('viewAny', App\Models\AiRun::class)
            <x-ai.action label="Review Opportunity" :url="route('ai.deep.opportunity')" :payload="['opportunity_id' => $opportunity->id]"
                intro="A specialist analysis of this deal: why it may close, blockers, missing information, next steps, strongest follow-up and anticipated objections — all for your review." />
        @endcan
        @can('update', $opportunity)
            <a href="{{ route('opportunities.edit', $opportunity) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3">
            <h2 class="text-lg font-bold text-ink-900">{{ $opportunity->name }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Stage</dt><dd><span class="badge-{{ $opportunity->stage === 'won' ? 'green' : ($opportunity->stage === 'lost' ? 'red' : 'blue') }}">{{ \App\Models\Opportunity::STAGES[$opportunity->stage] }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Value</dt><dd>{{ number_format((float) $opportunity->expected_value, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Probability</dt><dd>{{ $opportunity->probability ? round((float) $opportunity->probability, 0).'%' : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Close Date</dt><dd>{{ $opportunity->expected_close_date?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Assigned To</dt><dd>{{ $opportunity->assignedTo?->name ?? '—' }}</dd></div>
                @if ($opportunity->source)<div class="flex justify-between"><dt class="text-ink-400">Source</dt><dd>{{ $opportunity->source }}</dd></div>@endif
            </dl>
            @if ($opportunity->notes)
                <div class="rounded-lg bg-ink-50 p-3 text-sm text-ink-600">{{ $opportunity->notes }}</div>
            @endif
            @if ($opportunity->customer)
                <a href="{{ route('customers.show', $opportunity->customer) }}" class="block rounded-lg border border-ink-200 p-3 hover:bg-ink-50">
                    <p class="text-xs text-ink-400">Customer</p>
                    <p class="text-sm font-semibold text-navy-600">{{ $opportunity->customer->name }}</p>
                </a>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Activities</h3>
            @forelse ($opportunity->activities as $activity)
                <div class="border-l-2 border-navy-100 py-1 pl-4">
                    <p class="text-sm text-ink-800"><span class="font-semibold">{{ ucfirst($activity->type) }}</span> — {{ $activity->title }}</p>
                    <p class="text-xs text-ink-400">{{ $activity->due_at?->format('d M Y, H:i') }} @if ($activity->assignedTo) by {{ $activity->assignedTo->name }}@endif</p>
                </div>
            @empty
                <p class="text-sm text-ink-400">No activities logged.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
