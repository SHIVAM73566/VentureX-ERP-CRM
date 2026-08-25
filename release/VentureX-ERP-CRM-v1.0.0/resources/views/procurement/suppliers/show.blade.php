<x-layouts.app title="{{ $supplier->name }}" :breadcrumbs="[['label' => 'Procurement', 'url' => route('suppliers.index')], ['label' => 'Suppliers', 'url' => route('suppliers.index')], ['label' => $supplier->name]]">

    <x-slot name="actions">
        @can('viewAny', App\Models\AiRun::class)
            <x-ai.action label="AI analysis" :url="route('ai.actions.supplier-analysis')" :payload="['supplier_id' => $supplier->id]" />
            <x-ai.action label="Deep Analyze" :url="route('ai.deep.supplier')" :payload="['supplier_id' => $supplier->id]"
                intro="A specialist analysis of this supplier: strengths, risks, pricing and delivery observations, negotiation opportunities and a recommended next action — all for your review." />
            <x-ai.action label="Negotiation Strategy" :url="route('ai.deep.negotiation')" :payload="['supplier_id' => $supplier->id]"
                intro="A negotiation strategy built from this supplier's pricing history, terms and volumes — target price, points, concessions, questions and a draft email. Nothing is sent automatically." />
        @endcan
        @can('update', $supplier)
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('create', App\Models\SupplierOffer::class)
            <a href="{{ route('supplier-offers.create') }}" class="btn-accent">New Offer</a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3">
            <h2 class="text-lg font-bold text-ink-900">{{ $supplier->name }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-{{ $supplier->status === 'approved' ? 'green' : ($supplier->status === 'blocked' || $supplier->status === 'rejected' ? 'red' : 'amber') }}">{{ \App\Models\Supplier::STATUSES[$supplier->status] }}</span></dd></div>
                @if ($supplier->supplier_code)<div class="flex justify-between"><dt class="text-ink-400">Code</dt><dd>{{ $supplier->supplier_code }}</dd></div>@endif
                @if ($supplier->tax_id)<div class="flex justify-between"><dt class="text-ink-400">Tax ID</dt><dd>{{ $supplier->tax_id }}</dd></div>@endif
                @if ($supplier->contact_person)<div class="flex justify-between"><dt class="text-ink-400">Contact</dt><dd>{{ $supplier->contact_person }}</dd></div>@endif
                @if ($supplier->email)<div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd>{{ $supplier->email }}</dd></div>@endif
                @if ($supplier->phone)<div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd>{{ $supplier->phone }}</dd></div>@endif
                @if ($supplier->country)<div class="flex justify-between"><dt class="text-ink-400">Country</dt><dd>{{ $supplier->country->name }}</dd></div>@endif
                @if ($supplier->payment_terms)<div class="flex justify-between"><dt class="text-ink-400">Payment</dt><dd>{{ $supplier->payment_terms }}</dd></div>@endif
            </dl>
            @if ($supplier->notes)
                <div class="rounded-lg bg-ink-50 p-3 text-sm text-ink-600">{{ $supplier->notes }}</div>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Offers ({{ $supplier->offers_count }})</h3>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Material</th><th>Qty (MT)</th><th>Price/MT</th><th>Status</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($offers as $offer)
                            <tr>
                                <td class="font-medium text-ink-800">{{ $offer->material_category }}@if ($offer->grade) <span class="text-ink-400">({{ $offer->grade }})</span>@endif</td>
                                <td>{{ number_format((float) $offer->quantity_mt, 2) }}</td>
                                <td>{{ number_format((float) $offer->price_per_mt, 2) }}</td>
                                <td><span class="badge-{{ $offer->quality_status === 'GREEN' ? 'green' : ($offer->quality_status === 'RED' ? 'red' : 'amber') }}">{{ $offer->quality_status }}</span></td>
                                <td>{{ $offer->offer_date?->format('d M Y') ?? '—' }}</td>
                                <td class="text-right"><a href="{{ route('supplier-offers.show', $offer) }}" class="text-sm font-medium text-navy-600">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-4 text-center text-ink-400">No offers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $offers->links() }}</div>
        </div>
    </div>
</x-layouts.app>
