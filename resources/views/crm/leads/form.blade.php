<x-layouts.app
    :title="$lead->exists ? 'Edit Lead' : 'New Lead'"
    :breadcrumbs="[['label' => 'CRM', 'url' => route('leads.index')], ['label' => 'Leads', 'url' => route('leads.index')], ['label' => $lead->exists ? 'Edit' : 'New']]">

    <form method="POST" action="{{ $lead->exists ? route('leads.update', $lead) : route('leads.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($lead->exists)
            @method('PUT')
        @endif

        <div class="card space-y-4">
            <h2 class="text-lg font-bold text-ink-900">Lead Details</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.input label="Contact Name" name="contact_name" :value="$lead->contact_name" required />
                <x-form.input label="Company Name" name="company_name" :value="$lead->company_name" />
                <x-form.input label="Email" name="email" type="email" :value="$lead->email" />
                <x-form.input label="Phone" name="phone" :value="$lead->phone" />
                <x-form.select label="Source" name="source" :options="array_combine($sources, $sources)" :value="$lead->source" />
                <x-form.select label="Status" name="status" :options="$statuses" :value="$lead->status" />
                <x-form.input label="Estimated Value" name="estimated_value" type="number" step="0.01" :value="$lead->estimated_value" />
                <x-form.input label="Score" name="score" type="number" :value="$lead->score" />
                <x-form.select label="Assigned To" name="assigned_to" :options="$users->pluck('name', 'id')" :value="$lead->assigned_to" />
                <x-form.input label="Next Follow-up" name="next_follow_up" type="date" :value="$lead->next_follow_up?->format('Y-m-d')" />
                <x-form.select label="Country" name="country_id" :options="$countries->pluck('name', 'id')" :value="$lead->country_id" />
                <x-form.input label="Website" name="website" :value="$lead->website" />
                <div class="sm:col-span-2">
                    <x-form.textarea label="Notes" name="notes" :value="$lead->notes" rows="3" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('leads.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-accent">{{ $lead->exists ? 'Save Changes' : 'Create Lead' }}</button>
        </div>
    </form>
</x-layouts.app>
