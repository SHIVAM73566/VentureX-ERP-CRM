<x-layouts.app
    :title="$opportunity->exists ? 'Edit Opportunity' : 'New Opportunity'"
    :breadcrumbs="[['label' => 'CRM', 'url' => route('opportunities.index')], ['label' => 'Opportunities', 'url' => route('opportunities.index')], ['label' => $opportunity->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $opportunity->exists ? route('opportunities.update', $opportunity) : route('opportunities.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($opportunity->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Opportunity Details</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input label="Opportunity Name" name="name" :value="$opportunity->name" required />
                </div>
                <x-form.select label="Customer" name="customer_id" :options="$customers->pluck('name', 'id')" :value="$opportunity->customer_id" />
                <x-form.select label="Stage" name="stage" :options="$stages" :value="$opportunity->stage" />
                <x-form.input label="Expected Value" name="expected_value" type="number" step="0.01" :value="$opportunity->expected_value" />
                <x-form.input label="Probability (%)" name="probability" type="number" step="0.01" :value="$opportunity->probability" />
                <x-form.input label="Expected Close Date" name="expected_close_date" type="date" :value="$opportunity->expected_close_date?->format('Y-m-d')" />
                <x-form.select label="Assigned To" name="assigned_to" :options="$users->pluck('name', 'id')" :value="$opportunity->assigned_to" />
                <x-form.select label="Lead Source" name="source" :options="['Website' => 'Website', 'Referral' => 'Referral', 'Existing Customer' => 'Existing Customer', 'Trade Show' => 'Trade Show', 'Other' => 'Other']" :value="$opportunity->source" />
                <div class="sm:col-span-2">
                    <x-form.textarea label="Notes" name="notes" :value="$opportunity->notes" rows="3" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('opportunities.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $opportunity->exists ? 'Save Changes' : 'Create Opportunity' }}</button>
        </div>
    </form>
</x-layouts.app>
