<x-layouts.app
    :title="$account->exists ? 'Edit Account' : 'New Account'"
    :breadcrumbs="[['label' => 'Finance', 'url' => route('finance.accounts.index')], ['label' => 'Accounts', 'url' => route('finance.accounts.index')], ['label' => $account->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ $account->exists ? route('finance.accounts.update', $account) : route('finance.accounts.store') }}" class="space-y-6">
            @csrf
            @if ($account->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Account Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Code *" name="code" :value="$account->code" required placeholder="e.g. 1000" />
                    <x-form.input label="Name *" name="name" :value="$account->name" required />
                    <x-form.select label="Type *" name="type" :options="\App\Models\Account::TYPES" :value="$account->type" required />
                    <x-form.select label="Parent Account" name="parent_id" :options="$parents->pluck('name', 'id')" :value="$account->parent_id" />
                </div>
                <x-form.checkbox name="is_active" label="Active" description="Inactive accounts are hidden from new journal entries." :checked="$account->exists ? $account->is_active : true" />
                <x-form.textarea label="Description" name="description" :value="$account->description" rows="2" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('finance.accounts.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $account->exists ? 'Save Changes' : 'Create Account' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
