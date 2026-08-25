<x-layouts.app
    :title="$customer->exists ? 'Edit Customer' : 'New Customer'"
    :breadcrumbs="[['label' => 'CRM', 'url' => route('customers.index')], ['label' => 'Customers', 'url' => route('customers.index')], ['label' => $customer->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($customer->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Company Information</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input label="Company Name" name="name" :value="$customer->name" required />
                </div>
                <x-form.input label="Customer Code" name="customer_code" :value="$customer->customer_code" />
                <x-form.select label="Branch" name="branch_id" :options="$branches->pluck('name', 'id')" :value="$customer->branch_id" />
                <x-form.input label="Tax ID / VAT" name="tax_id" :value="$customer->tax_id" />
                <x-form.input label="Industry" name="industry" :value="$customer->industry" />
                <x-form.input label="Website" name="website" :value="$customer->website" />
                <x-form.select label="Status" name="status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'prospect' => 'Prospect']" :value="$customer->status" />
                <x-form.input label="Address Line 1" name="address_line1" :value="$customer->address_line1" />
                <x-form.input label="City" name="city" :value="$customer->city" />
                <x-form.input label="State" name="state" :value="$customer->state" />
                <x-form.input label="Postal Code" name="postal_code" :value="$customer->postal_code" />
                <x-form.select label="Country" name="country_id" :options="$countries->pluck('name', 'id')" :value="$customer->country_id" />
                <x-form.input label="Currency" name="currency_code" :value="$customer->currency_code" />
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Primary Contact</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.input label="Primary Contact Name" name="primary_contact_name" :value="$customer->name" />
                <x-form.input label="Phone" name="phone" :value="$customer->phone" />
                <x-form.input label="Email" name="email" type="email" :value="$customer->email" />
                <x-form.textarea label="Notes" name="notes" :value="$customer->notes" rows="3" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('customers.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $customer->exists ? 'Save Changes' : 'Create Customer' }}</button>
        </div>
    </form>
</x-layouts.app>
