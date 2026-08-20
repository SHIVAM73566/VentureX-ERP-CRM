<x-layouts.app
    :title="$requisition->exists ? 'Edit Requisition' : 'New Requisition'"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('procurement.requisitions.index')], ['label' => 'Requisitions', 'url' => route('procurement.requisitions.index')], ['label' => $requisition->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ $requisition->exists ? route('procurement.requisitions.update', $requisition) : route('procurement.requisitions.store') }}" class="space-y-6" x-data="requisitionForm()">
            @csrf
            @if ($requisition->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Requisition Details</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-form.select label="Department" name="department_id" :options="$departments->pluck('name', 'id')" :value="$requisition->department_id" />
                    <x-form.select label="Priority *" name="priority" :options="\App\Models\PurchaseRequisition::PRIORITIES" :value="$requisition->priority ?? 'medium'" required />
                    <x-form.input label="Required Date" name="required_date" type="date" :value="$requisition->required_date?->format('Y-m-d')" />
                    <x-form.select label="Status" name="status" :options="\App\Models\PurchaseRequisition::STATUSES" :value="$requisition->status ?? 'pending_approval'" class="sm:col-span-3" />
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Items</h2>
                    <button type="button" @click="addItem()" class="btn-secondary">+ Add Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Description</th><th class="w-28">Qty</th><th class="w-24">Unit</th><th class="w-40">Notes</th><th class="w-10"></th></tr></thead>
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
                                    <td><input type="text" :name="'items['+index+'][description]'" x-model="item.description" required placeholder="What is needed?" class="input" /></td>
                                    <td><input type="number" step="0.0001" min="0" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" class="input" /></td>
                                    <td><input type="text" :name="'items['+index+'][unit]'" x-model="item.unit" placeholder="kg, m, pcs" class="input" /></td>
                                    <td><input type="text" :name="'items['+index+'][notes]'" x-model="item.notes" placeholder="Specs / brand" class="input" /></td>
                                    <td><button type="button" @click="items.splice(index, 1)" class="text-red-500 hover:text-red-400">✕</button></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="6" class="py-6 text-center text-ink-400">No items yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Notes</h2>
                <x-form.textarea label="Notes" name="notes" :value="$requisition->notes" rows="3" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('procurement.requisitions.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $requisition->exists ? 'Save Changes' : 'Create Requisition' }}</button>
            </div>
        </form>
    </div>

    @php
        $seedItems = ($requisition->items ?? collect())->map(fn ($i) => [
            'product_id' => $i->product_id ?? '',
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit' => $i->unit ?? '',
            'notes' => $i->notes ?? '',
        ])->values()->all();
    @endphp

    <script>
        function requisitionForm() {
            const seed = @json($seedItems);

            return {
                items: seed.length ? seed : [{ product_id: '', description: '', quantity: 1, unit: '', notes: '' }],
                addItem() { this.items.push({ product_id: '', description: '', quantity: 1, unit: '', notes: '' }); },
            };
        }
    </script>
</x-layouts.app>
