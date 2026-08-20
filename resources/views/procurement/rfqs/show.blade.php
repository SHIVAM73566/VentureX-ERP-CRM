<x-layouts.app
    :title="'RFQ — '.($rfq->rfq_number ?? 'New')"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('procurement.rfqs.index')], ['label' => 'RFQs', 'url' => route('procurement.rfqs.index')], ['label' => $rfq->rfq_number]]">

    <x-slot name="actions">
        @can('update', $rfq)
            <a href="{{ route('procurement.rfqs.edit', $rfq) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $rfq)
            <form method="POST" action="{{ route('procurement.rfqs.destroy', $rfq) }}" onsubmit="return confirm('Delete this RFQ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">RFQ Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $rfq->rfq_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Title</dt><dd class="text-right">{{ $rfq->title }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Issued</dt><dd>{{ $rfq->issued_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Closes</dt><dd>{{ $rfq->closes_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@php $sc = ['draft' => 'gray', 'sent' => 'blue', 'open' => 'amber', 'awarded' => 'green', 'cancelled' => 'red']; @endphp
                            <span class="badge-{{ $sc[$rfq->status] ?? 'gray' }}">{{ \App\Models\Rfq::STATUSES[$rfq->status] ?? $rfq->status }}</span></dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Items</dt><dd class="font-bold text-ink-900">{{ $rfq->items->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Responses</dt><dd>{{ $rfq->responses->count() }}</dd></div>
                </dl>
            </div>

            @if ($rfq->description)
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Description</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $rfq->description }}</p></div>
            @endif

            @can('create', App\Models\PurchaseOrder::class)
                @if ($rfq->status !== 'cancelled' && $rfq->responses->count())
                    <div class="card">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Create Purchase Order</h3>
                        <form method="POST" action="{{ route('procurement.rfqs.create-order', $rfq) }}" class="space-y-3">
                            @csrf
                            <x-form.select label="Award Supplier" name="supplier_id" :options="$rfq->responses->mapWithKeys(fn ($r) => [$r->supplier_id => $r->supplier->name])" required />
                            <x-form.input label="PO Number (optional)" name="po_number" placeholder="Leave blank to auto-generate" />
                            <button type="submit" class="btn-accent w-full">Award &amp; Create Order</button>
                        </form>
                    </div>
                @endif
            @endcan
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>#</th><th>Product</th><th>Description</th><th class="text-right">Qty</th><th>Unit</th></tr></thead>
                        <tbody>
                            @forelse ($rfq->items as $item)
                                <tr>
                                    <td class="text-ink-400">{{ $loop->iteration }}</td>
                                    <td>{{ $item->product?->name ?? '—' }}</td>
                                    <td class="text-ink-700">{{ $item->description }}</td>
                                    <td class="text-right font-semibold text-ink-800">{{ number_format((float) $item->quantity, 4) }}</td>
                                    <td>{{ $item->unit ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No items.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Supplier Responses</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Supplier</th><th class="text-right">Amount</th><th>Delivery</th><th>Valid Until</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($rfq->responses as $response)
                                <tr>
                                    <td class="font-semibold text-ink-800">{{ $response->supplier?->name ?? '—' }}</td>
                                    <td class="text-right">{{ $response->amount !== null ? number_format((float) $response->amount, 2) : '—' }}</td>
                                    <td>{{ $response->delivery_time_days !== null ? $response->delivery_time_days.' days' : '—' }}</td>
                                    <td>{{ $response->valid_until?->format('d M Y') ?? '—' }}</td>
                                    <td>@php $rc = ['submitted' => 'blue', 'awarded' => 'green', 'rejected' => 'red']; @endphp
                                        <span class="badge-{{ $rc[$response->status] ?? 'gray' }}">{{ \App\Models\RfqResponse::STATUSES[$response->status] ?? $response->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No responses yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
