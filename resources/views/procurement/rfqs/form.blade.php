<x-layouts.app
    :title="$rfq->exists ? 'Edit RFQ' : 'New RFQ'"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('procurement.rfqs.index')], ['label' => 'RFQs', 'url' => route('procurement.rfqs.index')], ['label' => $rfq->exists ? 'Edit' : 'New']]">

    @php
        $selectedSuppliers = $rfq->exists ? $rfq->responses->pluck('supplier_id')->map(fn ($v) => (string) $v)->all() : [];
    @endphp

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ $rfq->exists ? route('procurement.rfqs.update', $rfq) : route('procurement.rfqs.store') }}" class="space-y-6" x-data="rfqForm()">
            @csrf
            @if ($rfq->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">RFQ Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Title *" name="title" :value="$rfq->title" required placeholder="e.g. Raw steel requirement Q3" />
                    <x-form.select label="Status" name="status" :options="\App\Models\Rfq::STATUSES" :value="$rfq->status ?? 'draft'" />
                    <x-form.input label="Issued At" name="issued_at" type="date" :value="$rfq->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d')" />
                    <x-form.input label="Closes At" name="closes_at" type="date" :value="$rfq->closes_at?->format('Y-m-d')" />
                </div>
                <x-form.textarea label="Description" name="description" :value="$rfq->description" rows="2" />
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Invite Suppliers</h2>
                <div class="grid gap-2 sm:grid-cols-2">
                    @forelse ($suppliers as $supplier)
                        <label class="flex items-center gap-2 rounded-lg border border-ink-200 px-3 py-2 text-sm hover:border-navy-400">
                            <input type="checkbox" name="supplier_ids[]" value="{{ $supplier->id }}" @checked(in_array((string) $supplier->id, $selectedSuppliers, true)) class="rounded border-ink-300" />
                            <span class="font-medium text-ink-800">{{ $supplier->name }}</span>
                            <span class="ml-auto text-xs text-ink-400">{{ $supplier->contact_person }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-ink-400 sm:col-span-2">No suppliers yet — create suppliers under Procurement first.</p>
                    @endforelse
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Line Items</h2>
                    <button type="button" @click="addItem()" class="btn-secondary">+ Add Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Description</th><th class="w-28">Qty</th><th class="w-24">Unit</th><th class="w-10"></th></tr></thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td>
                                        <select :name="'items['+index+'][product_id]'" x-model="item.product_id" class="input">
                                            <option value="">Product…</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku ?? 'no sku' }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" :name="'items['+index+'][description]'" x-model="item.description" required placeholder="Item" class="input" /></td>
                                    <td><input type="number" step="0.0001" min="0" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" class="input" /></td>
                                    <td><input type="text" :name="'items['+index+'][unit]'" x-model="item.unit" placeholder="kg" class="input" /></td>
                                    <td><button type="button" @click="items.splice(index, 1)" class="text-red-500 hover:text-red-400">✕</button></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="5" class="py-6 text-center text-ink-400">No items yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('procurement.rfqs.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $rfq->exists ? 'Save Changes' : 'Create RFQ' }}</button>
            </div>
        </form>
    </div>

    @php
        $seedItems = ($rfq->items ?? collect())->map(fn ($i) => [
            'product_id' => $i->product_id ?? '',
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit' => $i->unit ?? '',
        ])->values()->all();
    @endphp

    <script>
        function rfqForm() {
            const seed = @json($seedItems);

            return {
                items: seed.length ? seed : [{ product_id: '', description: '', quantity: 1, unit: '' }],
                addItem() { this.items.push({ product_id: '', description: '', quantity: 1, unit: '' }); },
            };
        }
    </script>
</x-layouts.app>
