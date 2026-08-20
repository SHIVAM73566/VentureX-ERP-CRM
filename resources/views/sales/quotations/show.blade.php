<x-layouts.app
    :title="'Quotation — '.($quotation->quotation_number ?? 'New')"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.quotations.index')], ['label' => 'Quotations', 'url' => route('sales.quotations.index')], ['label' => $quotation->quotation_number]]">

    <x-slot name="actions">
        <a href="{{ route('sales.quotations.pdf', $quotation) }}" class="btn-secondary" target="_blank">Download PDF</a>
        @if($quotation->status === 'accepted')
            <form method="POST" action="{{ route('sales.quotations.convert', $quotation) }}" onsubmit="return confirm('Convert this quotation to a sales order?')">
                @csrf
                <button type="submit" class="btn-primary">Convert to Sales Order</button>
            </form>
        @endif
        @can('update', $quotation)
            <a href="{{ route('sales.quotations.edit', $quotation) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $quotation)
            <form method="POST" action="{{ route('sales.quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this quotation?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Quotation Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $quotation->quotation_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd class="text-right">{{ $quotation->customer?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Created</dt><dd>{{ $quotation->created_at?->format('d M Y H:i') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Valid Until</dt><dd>{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                        <dd>@php $c = ['draft' => 'gray', 'sent' => 'amber', 'accepted' => 'green', 'rejected' => 'red', 'expired' => 'gray', 'converted' => 'blue']; @endphp
                            <span class="badge-{{ $c[$quotation->status] ?? 'gray' }}">{{ \App\Models\Quotation::STATUSES[$quotation->status] ?? $quotation->status }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-bold text-ink-900">{{ number_format((float) $quotation->total, 2) }} {{ $quotation->currency_code ?? 'USD' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Line Items</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th class="text-right">Line Total</th></tr></thead>
                        <tbody>
                            @forelse ($quotation->items as $item)
                                <tr>
                                    <td><p class="font-medium text-ink-800">{{ $item->description }}</p>@if ($item->product)<p class="text-xs text-ink-400">{{ $item->product->sku ?? '' }} {{ $item->product->name }}</p>@endif</td>
                                    <td>{{ number_format((float) $item->quantity, 3) }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ (float) $item->tax_rate }}%</td>
                                    <td class="text-right font-semibold text-ink-800">{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-ink-400">No items.</td></tr>
                            @endforelse
                            <tr class="border-t-2 border-ink-200">
                                <td colspan="4" class="text-right font-semibold text-ink-700">Subtotal / Discount / Tax</td>
                                <td class="text-right font-semibold">{{ number_format((float) $quotation->subtotal, 2) }} / {{ number_format((float) $quotation->discount, 2) }} / {{ number_format((float) $quotation->tax, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right font-bold text-ink-900">Total</td>
                                <td class="text-right font-bold text-ink-900">{{ number_format((float) $quotation->total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($quotation->notes || $quotation->terms)
                <div class="card space-y-3">
                    @if ($quotation->notes)<div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $quotation->notes }}</p></div>@endif
                    @if ($quotation->terms)<div><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Terms</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700">{{ $quotation->terms }}</p></div>@endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
