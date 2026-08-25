<x-layouts.app
    :title="$invoice->exists ? 'Edit Invoice' : 'New Invoice'"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.invoices.index')], ['label' => 'Invoices', 'url' => route('sales.invoices.index')], ['label' => $invoice->exists ? 'Edit' : 'New']]">

    @php
        $initialOrder = $invoice->exists ? $invoice->sales_order_id : ($presetOrder ?? null)?->id;
        $initialCustomer = $invoice->exists ? $invoice->customer_id : ($presetOrder ?? null)?->customer_id;
    @endphp

    <div class="mx-auto max-w-5xl">
        <form method="POST" action="{{ $invoice->exists ? route('sales.invoices.update', $invoice) : route('sales.invoices.store') }}" class="space-y-6" x-data="invoiceForm({{ json_encode($initialOrder) }}, {{ json_encode($initialCustomer) }})">
            @csrf
            @if ($invoice->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Invoice Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Customer *" name="customer_id" :options="$customers->pluck('name', 'id')" :value="$initialCustomer" required />
                    <x-form.select label="Source Order" name="sales_order_id" :options="$orders->pluck('order_number', 'id')" :value="$initialOrder" />
                    <x-form.select label="Status" name="status" :options="\App\Models\Invoice::STATUSES" :value="$invoice->status ?? 'draft'" />
                    <x-form.input label="Paid Amount" name="paid_amount" type="number" step="0.01" :value="$invoice->paid_amount ?? 0" />
                    <x-form.input label="Issue Date *" name="issue_date" type="date" :value="$invoice->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
                    <x-form.input label="Due Date" name="due_date" type="date" :value="$invoice->due_date?->format('Y-m-d')" />
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Line Items</h2>
                    <button type="button" @click="addItem()" class="btn-secondary">+ Add Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th class="min-w-[10rem]">Description</th><th class="w-24">Qty</th><th class="w-32">Unit Price</th><th class="w-28">Discount</th><th class="w-24">Tax %</th><th class="w-32">Line Total</th><th class="w-10"></th></tr></thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td>
                                        <select :name="'items['+index+'][product_id]'" x-model="item.product_id" class="input mb-1">
                                            <option value="">Product…</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" :name="'items['+index+'][description]'" x-model="item.description" required placeholder="Description" class="input" />
                                    </td>
                                    <td><input type="number" step="0.001" min="0" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'items['+index+'][discount]'" x-model.number="item.discount" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'items['+index+'][tax_rate]'" x-model.number="item.tax_rate" class="input" /></td>
                                    <td class="text-right font-semibold text-ink-800" x-text="lineTotal(item).toFixed(2)"></td>
                                    <td><button type="button" @click="items.splice(index, 1)" class="text-red-500 hover:text-red-400">✕</button></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="7" class="py-6 text-center text-ink-400">No items yet.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="ml-auto w-full max-w-xs space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-ink-400">Subtotal</span><span class="font-semibold text-ink-800" x-text="totals().subtotal.toFixed(2)"></span></div>
                    <div class="flex justify-between"><span class="text-ink-400">Discount</span><span class="font-semibold text-ink-800" x-text="'(' + totals().discount.toFixed(2) + ')'"></span></div>
                    <div class="flex justify-between"><span class="text-ink-400">Tax</span><span class="font-semibold text-ink-800" x-text="totals().tax.toFixed(2)"></span></div>
                    <div class="flex justify-between border-t border-ink-200 pt-2"><span class="font-bold text-ink-900">Total</span><span class="font-bold text-ink-900" x-text="totals().total.toFixed(2)"></span></div>
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Notes</h2>
                <x-form.textarea label="Notes" name="notes" :value="$invoice->notes" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('sales.invoices.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $invoice->exists ? 'Save Changes' : 'Create Invoice' }}</button>
            </div>
        </form>
    </div>

    @php
        $seedItems = ($invoice->items ?? collect())->map(fn ($i) => [
            'product_id' => $i->product_id ?? '',
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'discount' => (float) $i->discount,
            'tax_rate' => (float) $i->tax_rate,
        ])->values()->all();
        $orderItems = ($presetOrder ?? null)?->items?->map(fn ($i) => [
            'product_id' => $i->product_id ?? '',
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'discount' => (float) $i->discount,
            'tax_rate' => (float) $i->tax_rate,
        ])->values()->all() ?? [];
    @endphp

    <script>
        function invoiceForm(initialOrder, initialCustomer) {
            const seed = @json($seedItems);

            const orderItems = @json($orderItems);

            return {
                items: seed.length ? seed : (orderItems.length ? orderItems : [{ product_id: '', description: '', quantity: 1, unit_price: 0, discount: 0, tax_rate: 0 }]),
                addItem() { this.items.push({ product_id: '', description: '', quantity: 1, unit_price: 0, discount: 0, tax_rate: 0 }); },
                lineTotal(item) {
                    const net = (item.quantity || 0) * (item.unit_price || 0) - (item.discount || 0);
                    return net * (1 + (item.tax_rate || 0) / 100);
                },
                totals() {
                    let subtotal = 0, discount = 0, tax = 0;
                    this.items.forEach(i => {
                        const net = (i.quantity || 0) * (i.unit_price || 0);
                        subtotal += net;
                        discount += (i.discount || 0);
                        tax += (net - (i.discount || 0)) * ((i.tax_rate || 0) / 100);
                    });
                    return { subtotal, discount, tax, total: subtotal - discount + tax };
                },
            };
        }
    </script>
</x-layouts.app>
