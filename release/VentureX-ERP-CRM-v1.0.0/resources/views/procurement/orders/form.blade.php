<x-layouts.app
    :title="$order->exists ? 'Edit Purchase Order' : 'New Purchase Order'"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('procurement.orders.index')], ['label' => 'Orders', 'url' => route('procurement.orders.index')], ['label' => $order->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-5xl">
        <form method="POST" action="{{ $order->exists ? route('procurement.orders.update', $order) : route('procurement.orders.store') }}" class="space-y-6" x-data="orderForm({{ json_encode($order->rfq_id ?? null) }})">
            @csrf
            @if ($order->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Order Details</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-form.select label="Supplier *" name="supplier_id" :options="$suppliers->pluck('name', 'id')" :value="$order->supplier_id" required />
                    <x-form.select label="Source RFQ" name="rfq_id" :options="$rfqs->pluck('rfq_number', 'id')" :value="$order->rfq_id" />
                    <x-form.input label="Order Date *" name="order_date" type="date" :value="$order->order_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
                    <x-form.input label="Expected Date" name="expected_date" type="date" :value="$order->expected_date?->format('Y-m-d')" />
                    <x-form.select label="Status" name="status" :options="\App\Models\PurchaseOrder::STATUSES" :value="$order->status ?? 'draft'" />
                    <x-form.select label="Payment Status" name="payment_status" :options="\App\Models\PurchaseOrder::PAYMENT_STATUSES" :value="$order->payment_status ?? 'unpaid'" />
                    <x-form.input label="Discount" name="discount" type="number" step="0.01" min="0" :value="$order->discount" />
                    <x-form.input label="Shipping" name="shipping" type="number" step="0.01" min="0" :value="$order->shipping" />
                    <x-form.input label="Paid Amount" name="paid_amount" type="number" step="0.01" min="0" :value="$order->paid_amount ?? 0" />
                    <x-form.input label="Payment Terms" name="payment_terms" :value="$order->payment_terms" placeholder="Net 30, COD…" class="sm:col-span-3" />
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Line Items</h2>
                    <button type="button" @click="addItem()" class="btn-secondary">+ Add Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Description</th><th class="w-24">Qty</th><th class="w-20">Unit</th><th class="w-32">Unit Price</th><th class="w-24">Tax %</th><th class="w-32">Line Total</th><th class="w-10"></th></tr></thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td>
                                        <select :name="'items['+index+'][product_id]'" x-model="item.product_id" class="input mb-1">
                                            <option value="">Product…</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku ?? 'no sku' }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" :name="'items['+index+'][description]'" x-model="item.description" required placeholder="Item" class="input" /></td>
                                    <td><input type="number" step="0.0001" min="0" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" class="input" /></td>
                                    <td><input type="text" :name="'items['+index+'][unit]'" x-model="item.unit" placeholder="kg" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'items['+index+'][tax_rate]'" x-model.number="item.tax_rate" class="input" /></td>
                                    <td class="text-right font-semibold text-ink-800" x-text="lineTotal(item).toFixed(2)"></td>
                                    <td><button type="button" @click="items.splice(index, 1)" class="text-red-500 hover:text-red-400">✕</button></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="8" class="py-6 text-center text-ink-400">No items yet.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="ml-auto w-full max-w-xs space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-ink-400">Subtotal</span><span class="font-semibold text-ink-800" x-text="totals().subtotal.toFixed(2)"></span></div>
                    <div class="flex justify-between"><span class="text-ink-400">Tax</span><span class="font-semibold text-ink-800" x-text="totals().tax.toFixed(2)"></span></div>
                    <div class="flex justify-between border-t border-ink-200 pt-2"><span class="font-bold text-ink-900">Total (items)</span><span class="font-bold text-ink-900" x-text="totals().total.toFixed(2)"></span></div>
                    <p class="text-xs text-ink-400">Final total includes order-level discount & shipping entered above.</p>
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Notes</h2>
                <x-form.textarea label="Notes" name="notes" :value="$order->notes" rows="3" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('procurement.orders.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $order->exists ? 'Save Changes' : 'Create Purchase Order' }}</button>
            </div>
        </form>
    </div>

    @php
        $seedItems = ($order->items ?? collect())->map(fn ($i) => [
            'product_id' => $i->product_id ?? '',
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit' => $i->unit ?? '',
            'unit_price' => (float) $i->unit_price,
            'tax_rate' => (float) $i->tax_rate,
        ])->values()->all();
    @endphp

    <script>
        function orderForm(initialRfq) {
            const seed = @json($seedItems);

            return {
                items: seed.length ? seed : [{ product_id: '', description: '', quantity: 1, unit: '', unit_price: 0, tax_rate: 0 }],
                addItem() { this.items.push({ product_id: '', description: '', quantity: 1, unit: '', unit_price: 0, tax_rate: 0 }); },
                lineTotal(item) { return (item.quantity || 0) * (item.unit_price || 0) * (1 + (item.tax_rate || 0) / 100); },
                totals() {
                    let subtotal = 0, tax = 0;
                    this.items.forEach(i => {
                        subtotal += (i.quantity || 0) * (i.unit_price || 0);
                        tax += (i.quantity || 0) * (i.unit_price || 0) * ((i.tax_rate || 0) / 100);
                    });
                    return { subtotal, tax, total: subtotal + tax };
                },
            };
        }
    </script>
</x-layouts.app>
