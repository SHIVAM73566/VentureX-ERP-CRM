<x-layouts.app
    :title="'Requisition — '.($requisition->pr_number ?? 'New')"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('procurement.requisitions.index')], ['label' => 'Requisitions', 'url' => route('procurement.requisitions.index')], ['label' => $requisition->pr_number]]">

    <x-slot name="actions">
        @can('update', $requisition)
            <a href="{{ route('procurement.requisitions.edit', $requisition) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $requisition)
            <form method="POST" action="{{ route('procurement.requisitions.destroy', $requisition) }}" onsubmit="return confirm('Delete this requisition?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Requisition Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $requisition->pr_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Department</dt><dd>{{ $requisition->department?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Requester</dt><dd>{{ $requisition->requester?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Required</dt><dd>{{ $requisition->required_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Priority</dt>
                        <dd>@php $pc = ['low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'critical' => 'red']; @endphp
                            <span class="badge-{{ $pc[$requisition->priority] ?? 'gray' }}">{{ \App\Models\PurchaseRequisition::PRIORITIES[$requisition->priority] ?? $requisition->priority }}</span></dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@php $sc = ['draft' => 'gray', 'pending_approval' => 'amber', 'approved' => 'green', 'rejected' => 'red', 'ordered' => 'violet']; @endphp
                            <span class="badge-{{ $sc[$requisition->status] ?? 'gray' }}">{{ \App\Models\PurchaseRequisition::STATUSES[$requisition->status] ?? $requisition->status }}</span></dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Items</dt><dd class="font-bold text-ink-900">{{ $requisition->items->count() }}</dd></div>
                </dl>
            </div>

            @if ($requisition->notes)
                <div class="card"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $requisition->notes }}</p></div>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Items</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>#</th><th>Product</th><th>Description</th><th class="text-right">Qty</th><th>Unit</th><th>Notes</th></tr></thead>
                    <tbody>
                        @forelse ($requisition->items as $item)
                            <tr>
                                <td class="text-ink-400">{{ $loop->iteration }}</td>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="text-ink-700">{{ $item->description }}</td>
                                <td class="text-right font-semibold text-ink-800">{{ number_format((float) $item->quantity, 4) }}</td>
                                <td>{{ $item->unit ?? '—' }}</td>
                                <td class="text-ink-400">{{ $item->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-ink-400">No items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
