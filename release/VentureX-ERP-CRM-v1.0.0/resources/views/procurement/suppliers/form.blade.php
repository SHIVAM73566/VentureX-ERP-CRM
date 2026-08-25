<x-layouts.app
    :title="$supplier->exists ? 'Edit Supplier' : 'New Supplier'"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('suppliers.index')], ['label' => 'Suppliers', 'url' => route('suppliers.index')], ['label' => $supplier->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($supplier->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Supplier Details</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input label="Supplier Name" name="name" :value="$supplier->name" required />
                </div>
                <x-form.input label="Supplier Code" name="supplier_code" :value="$supplier->supplier_code" />
                <x-form.input label="Tax ID" name="tax_id" :value="$supplier->tax_id" />
                <x-form.input label="Contact Person" name="contact_person" :value="$supplier->contact_person" />
                <x-form.input label="Email" name="email" type="email" :value="$supplier->email" />
                <x-form.input label="Phone" name="phone" :value="$supplier->phone" />
                <x-form.input label="Website" name="website" :value="$supplier->website" />
                <x-form.select label="Country" name="country_id" :options="$countries->pluck('name', 'id')" :value="$supplier->country_id" />
                <x-form.input label="Currency" name="currency_code" :value="$supplier->currency_code" />
                <div class="sm:col-span-2">
                    <x-form.input label="Address" name="address_line1" :value="$supplier->address_line1" />
                </div>
                <x-form.input label="City" name="city" :value="$supplier->city" />
                <x-form.input label="State" name="state" :value="$supplier->state" />
                <x-form.input label="Postal Code" name="postal_code" :value="$supplier->postal_code" />
                <x-form.input label="Payment Terms" name="payment_terms" :value="$supplier->payment_terms" />
                <x-form.select label="Status" name="status" :options="\App\Models\Supplier::STATUSES" :value="$supplier->status" />
                <div class="sm:col-span-2">
                    <x-form.textarea label="Notes" name="notes" :value="$supplier->notes" rows="3" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $supplier->exists ? 'Save Changes' : 'Create Supplier' }}</button>
        </div>
    </form>
</x-layouts.app>
