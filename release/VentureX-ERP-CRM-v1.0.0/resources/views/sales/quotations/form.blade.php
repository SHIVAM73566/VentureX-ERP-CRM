<x-layouts.app
    :title="$quotation->exists ? 'Edit Quotation' : 'New Quotation'"
    :breadcrumbs="[['label' => 'Sales', 'url' => route('sales.quotations.index')], ['label' => 'Quotations', 'url' => route('sales.quotations.index')], ['label' => $quotation->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-5xl">
        <form method="POST" action="{{ $quotation->exists ? route('sales.quotations.update', $quotation) : route('sales.quotations.store') }}" class="space-y-6" x-data="quotationForm()">
            @csrf
            @if ($quotation->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Quotation Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Customer *" name="customer_id" :options="$customers->pluck('name', 'id')" :value="$quotation->customer_id" required />
                    <x-form.select label="Status" name="status" :options="\App\Models\Quotation::STATUSES" :value="$quotation->status ?? 'draft'" />
                    <x-form.input label="Valid Until" name="valid_until" type="date" :value="$quotation->valid_until?->format('Y-m-d')" />
                    <x-form.input label="Currency" name="currency_code" :value="$quotation->currency_code ?? 'USD'" />
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Line Items</h2>
                    <button type="button" @click="addItem()" class="btn-secondary">+ Add Item</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="min-w-[10rem]">Description</th>
                                <th class="w-24">Qty</th>
                                <th class="w-32">Unit Price</th>
                                <th class="w-28">Discount</th>
                                <th class="w-24">Tax %</th>
                                <th class="w-32">Line Total</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
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
                            <tr x-show="items.length === 0">
                                <td colspan="7" class="py-6 text-center text-ink-400">No items yet. Add a line item above.</td>
                            </tr>
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
                <h2 class="text-lg font-bold text-ink-900">Notes & Terms</h2>
                <x-form.textarea label="Notes" name="notes" :value="$quotation->notes" />
                <x-form.textarea label="Terms" name="terms" :value="$quotation->terms" rows="4" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('sales.quotations.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $quotation->exists ? 'Save Changes' : 'Create Quotation' }}</button>
            </div>
        </form>
    </div>

    @php
        $seedItems = ($quotation->items ?? collect())->map(fn ($i) => [
            'product_id' => $i->product_id ?? '',
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'discount' => (float) $i->discount,
            'tax_rate' => (float) $i->tax_rate,
        ])->values()->all();
    @endphp

    <script>
        function quotationForm() {
            const seed = @json($seedItems);

            return {
                items: seed.length ? seed : [{ product_id: '', description: '', quantity: 1, unit_price: 0, discount: 0, tax_rate: 0 }],
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
