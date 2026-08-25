<x-layouts.app
    :title="'Schedule Activity'"
    :breadcrumbs="[['label' => 'CRM', 'url' => route('crm.activities.index')], ['label' => 'Activities', 'url' => route('crm.activities.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('crm.activities.store') }}" class="space-y-6" x-data="{ subjectType: 'customer' }">
            @csrf

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Activity Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Title *" name="title" placeholder="e.g. Call about quotation" required />
                    <x-form.select label="Type *" name="type" :options="\App\Models\Activity::TYPES" required />
                    <x-form.select label="Assignee" name="assigned_to" :options="$users->pluck('name', 'id')" />
                    <x-form.input label="Due At" name="due_at" type="datetime-local" />
                </div>
                <x-form.textarea label="Description" name="description" rows="3" />
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Related To</h2>
                <div>
                    <label for="subject_type" class="label">Subject Type *</label>
                    <select id="subject_type" name="subject_type" x-model="subjectType" required class="input">
                        <option value="customer">Customer</option>
                        <option value="lead">Lead</option>
                        <option value="opportunity">Opportunity</option>
                    </select>
                </div>
                <div x-show="subjectType === 'customer'">
                    <x-form.select label="Customer *" name="subject_id" :options="$customers->pluck('name', 'id')" required />
                </div>
                <div x-show="subjectType === 'lead'" x-cloak>
                    <x-form.select label="Lead *" name="subject_id" :options="$leads->pluck('contact_name', 'id')" required />
                </div>
                <div x-show="subjectType === 'opportunity'" x-cloak>
                    <x-form.select label="Opportunity *" name="subject_id" :options="$opportunities->pluck('name', 'id')" required />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('crm.activities.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Schedule Activity</button>
            </div>
        </form>
    </div>
</x-layouts.app>
