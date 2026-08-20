<x-layouts.app
    :title="$company->exists ? 'Edit Company' : 'New Company'"
    :breadcrumbs="[['label' => 'Administration', 'url' => '#'], ['label' => 'Companies', 'url' => route('admin.companies.index')], ['label' => $company->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($company->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Company Details</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input label="Company Name" name="name" :value="$company->name" required />
                </div>
                <x-form.input label="Legal Name" name="legal_name" :value="$company->legal_name" />
                <x-form.input label="Tax ID / VAT" name="tax_id" :value="$company->tax_id" />
                <x-form.input label="Registration Number" name="registration_number" :value="$company->registration_number" />
                <x-form.input label="Currency" name="currency_code" :value="$company->currency_code ?? 'INR'" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input label="Address" name="address_line1" :value="$company->address_line1" />
                </div>
                <x-form.input label="City" name="city" :value="$company->city" />
                <x-form.input label="State" name="state" :value="$company->state" />
                <x-form.input label="Postal Code" name="postal_code" :value="$company->postal_code" />
                <x-form.select label="Country" name="country_id" :options="$countries->pluck('name', 'id')" :value="$company->country_id" />
                <x-form.input label="Phone" name="phone" :value="$company->phone" />
                <x-form.input label="Email" name="email" :value="$company->email" />
                <x-form.input label="Website" name="website" :value="$company->website" />
                <x-form.input label="Logo URL" name="logo_url" :value="$company->logo_url" />
            </div>

            <div>
                <x-form.checkbox name="is_active" label="Active" description="Inactive companies are hidden from active use." :checked="$company->is_active" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.companies.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $company->exists ? 'Save Changes' : 'Create Company' }}</button>
        </div>
    </form>
</x-layouts.app>
