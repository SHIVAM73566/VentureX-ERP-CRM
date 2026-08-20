<x-layouts.app title="{{ $lead->contact_name }}" :breadcrumbs="[['label' => 'CRM', 'url' => route('leads.index')], ['label' => 'Leads', 'url' => route('leads.index')], ['label' => $lead->lead_number]]">

    <x-slot name="actions">
        @can('viewAny', App\Models\AiRun::class)
            <x-ai.action label="AI email draft" :url="route('ai.actions.lead-email')" :payload="['lead_id' => $lead->id]" intro="AI will draft a professional follow-up email based on this lead. Review it before sending." />
        @endcan
        @can('update', $lead)
            <a href="{{ route('leads.edit', $lead) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3">
            <div>
                <h2 class="text-lg font-bold text-ink-900">{{ $lead->contact_name }}</h2>
                <p class="text-sm text-ink-400">{{ $lead->lead_number }}@if ($lead->company_name) • {{ $lead->company_name }}@endif</p>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-{{ $lead->status === 'won' ? 'green' : ($lead->status === 'lost' ? 'red' : ($lead->status === 'new' ? 'amber' : 'blue')) }}">{{ \App\Models\Lead::STATUSES[$lead->status] }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Source</dt><dd>{{ $lead->source ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Score</dt><dd>{{ $lead->score ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Value</dt><dd>{{ $lead->estimated_value ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Assigned To</dt><dd>{{ $lead->assignedTo?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Next Follow-up</dt><dd>{{ $lead->next_follow_up?->format('d M Y') ?? '—' }}</dd></div>
                @if ($lead->email)<div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd>{{ $lead->email }}</dd></div>@endif
                @if ($lead->phone)<div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd>{{ $lead->phone }}</dd></div>@endif
                @if ($lead->website)<div class="flex justify-between"><dt class="text-ink-400">Website</dt><dd>{{ $lead->website }}</dd></div>@endif
            </dl>
            @if ($lead->notes)
                <div class="rounded-lg bg-ink-50 p-3 text-sm text-ink-600">{{ $lead->notes }}</div>
            @endif
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Activities</h3>
                @forelse ($lead->activities as $activity)
                    <div class="border-l-2 border-navy-100 py-1 pl-4">
                        <p class="text-sm text-ink-800"><span class="font-semibold">{{ ucfirst($activity->type) }}</span> — {{ $activity->title }}</p>
                        <p class="text-xs text-ink-400">{{ $activity->due_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-ink-400">No activities logged.</p>
                @endforelse
            </div>

            @if ($lead->opportunity)
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Related Opportunity</h3>
                    <a href="{{ route('opportunities.show', $lead->opportunity) }}" class="font-medium text-navy-600 hover:text-navy-500">{{ $lead->opportunity->name }}</a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
