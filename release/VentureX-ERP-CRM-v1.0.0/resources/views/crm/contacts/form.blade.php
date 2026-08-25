<x-layouts.app
    :title="$contact->exists ? 'Edit Contact' : 'New Contact'"
    :breadcrumbs="[['label' => 'CRM', 'url' => route('crm.contacts.index')], ['label' => 'Contacts', 'url' => route('crm.contacts.index')], ['label' => $contact->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $contact->exists ? route('crm.contacts.update', $contact) : route('crm.contacts.store') }}" class="space-y-6">
            @csrf
            @if ($contact->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Contact Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="First Name *" name="first_name" :value="$contact->first_name" required />
                    <x-form.input label="Last Name" name="last_name" :value="$contact->last_name" />
                    <x-form.select label="Company" name="customer_id" :options="$customers->pluck('name', 'id')" :value="$contact->customer_id" />
                    <x-form.input label="Job Title" name="title" :value="$contact->title" />
                    <x-form.input label="Email" name="email" type="email" :value="$contact->email" />
                    <x-form.input label="Phone" name="phone" :value="$contact->phone" />
                    <x-form.input label="Mobile" name="mobile" :value="$contact->mobile" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.checkbox name="is_primary" label="Primary Contact" description="Primary point of contact for this company." :checked="$contact->is_primary" />
                    <x-form.checkbox name="is_active" label="Active" description="Inactive contacts are hidden from quick lists." :checked="$contact->exists ? $contact->is_active : true" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('crm.contacts.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $contact->exists ? 'Save Changes' : 'Create Contact' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
