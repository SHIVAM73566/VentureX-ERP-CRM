<x-layouts.app title="{{ $customer->name }}" :breadcrumbs="[['label' => 'CRM', 'url' => route('customers.index')], ['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->name]]">

    <x-slot name="actions">
        @can('viewAny', App\Models\AiRun::class)
            <x-ai.action label="AI summary" :url="route('ai.actions.customer-summary')" :payload="['customer_id' => $customer->id]" />
            <x-ai.action label="Deep Customer Analysis" :url="route('ai.deep.customer')" :payload="['customer_id' => $customer->id]"
                intro="A specialist analysis of this customer: health, revenue opportunity, retention risk, follow-up priority, upsell/cross-sell opportunities and a recommended next action — all for your review." />
        @endcan
        @can('update', $customer)
            <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">{{ $customer->name }}</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="{{ $customer->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($customer->status) }}</span></dd></div>
                    @if ($customer->email)<div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd>{{ $customer->email }}</dd></div>@endif
                    @if ($customer->phone)<div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd>{{ $customer->phone }}</dd></div>@endif
                    @if ($customer->website)<div class="flex justify-between"><dt class="text-ink-400">Website</dt><dd>{{ $customer->website }}</dd></div>@endif
                    @if ($customer->tax_id)<div class="flex justify-between"><dt class="text-ink-400">VAT</dt><dd>{{ $customer->tax_id }}</dd></div>@endif
                    @if ($customer->country)<div class="flex justify-between"><dt class="text-ink-400">Country</dt><dd>{{ $customer->country->name }}</dd></div>@endif
                </dl>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Contacts ({{ $customer->contacts_count }})</h3>
                @forelse ($customer->contacts as $contact)
                    <div class="py-2">
                        <p class="text-sm font-semibold text-ink-800">{{ $contact->fullName() }}</p>
                        <p class="text-xs text-ink-400">{{ $contact->email }}@if ($contact->phone) • {{ $contact->phone }}@endif</p>
                    </div>
                @empty
                    <p class="text-sm text-ink-400">No contacts.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Opportunities ({{ $customer->opportunities_count }})</h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr><th>Opportunity</th><th>Stage</th><th>Value</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->opportunities as $opportunity)
                                <tr>
                                    <td class="font-medium text-ink-800">{{ $opportunity->name }}</td>
                                    <td>{{ $opportunity->stage }}</td>
                                    <td>{{ $opportunity->expected_value }}</td>
                                    <td class="text-right"><a href="{{ route('opportunities.show', $opportunity) }}" class="text-sm font-medium text-navy-600">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-ink-400">No opportunities.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Recent Activities</h3>
                @forelse ($activities as $activity)
                    <div class="border-l-2 border-navy-100 py-1 pl-4">
                        <p class="text-sm text-ink-800"><span class="font-semibold">{{ ucfirst($activity->type) }}</span> — {{ $activity->title }}</p>
                        <p class="text-xs text-ink-400">{{ $activity->due_at?->format('d M Y, H:i') ?? '—' }} @if ($activity->assignedTo) by {{ $activity->assignedTo->name }}@endif</p>
                    </div>
                @empty
                    <p class="text-sm text-ink-400">No activities logged.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
