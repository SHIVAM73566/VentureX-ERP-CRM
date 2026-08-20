<x-layouts.app title="Chart of Accounts" :breadcrumbs="[['label' => 'Finance'], ['label' => 'Accounts']]">

    <x-slot name="actions">
        @can('create', App\Models\Account::class)
            <a href="{{ route('finance.accounts.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Account
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-5">
        @foreach ($totals as $type => $count)
            @php $colors = ['asset' => 'green', 'liability' => 'red', 'equity' => 'violet', 'income' => 'blue', 'expense' => 'amber']; @endphp
            <x-dashboard.stat-card :label="ucfirst($type).'s'" :value="$count" icon="folder" :color="$colors[$type] ?? 'gray'" />
        @endforeach
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('finance.accounts.index') }}" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or code..." class="input max-w-md" />
            <select name="type" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach (\App\Models\Account::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Parent</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td class="font-mono text-ink-600">{{ $account->code }}</td>
                            <td><a href="{{ route('finance.accounts.show', $account) }}" class="font-semibold text-navy-600 hover:text-navy-500">{{ $account->name }}</a></td>
                            <td><span class="badge-{{ $account->type === 'asset' ? 'green' : ($account->type === 'liability' ? 'red' : ($account->type === 'income' ? 'blue' : 'gray')) }}">{{ \App\Models\Account::TYPES[$account->type] ?? $account->type }}</span></td>
                            <td>{{ $account->parent?->name ?? '—' }}</td>
                            <td class="font-semibold text-ink-800">{{ number_format((float) $account->balance(), 2) }}</td>
                            <td><span class="badge-{{ $account->is_active ? 'green' : 'gray' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                @can('update', $account)
                                    <a href="{{ route('finance.accounts.edit', $account) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No accounts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $accounts->links() }}</div>
</x-layouts.app>
