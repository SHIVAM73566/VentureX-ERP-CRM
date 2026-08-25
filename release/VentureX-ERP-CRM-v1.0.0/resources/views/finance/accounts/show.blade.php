<x-layouts.app
    :title="'Account — '.($account->name ?? 'New')"
    :breadcrumbs="[['label' => 'Finance', 'url' => route('finance.accounts.index')], ['label' => 'Accounts', 'url' => route('finance.accounts.index')], ['label' => $account->name]]">

    <x-slot name="actions">
        @can('update', $account)
            <a href="{{ route('finance.accounts.edit', $account) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $account)
            <form method="POST" action="{{ route('finance.accounts.destroy', $account) }}" onsubmit="return confirm('Delete this account?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Account Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Code</dt><dd class="font-mono">{{ $account->code }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Name</dt><dd>{{ $account->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Type</dt><dd><span class="badge-{{ $account->type === 'asset' ? 'green' : ($account->type === 'liability' ? 'red' : ($account->type === 'income' ? 'blue' : 'gray')) }}">{{ \App\Models\Account::TYPES[$account->type] ?? $account->type }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Parent</dt><dd>{{ $account->parent?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-{{ $account->is_active ? 'green' : 'gray' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Balance</dt><dd class="font-bold text-ink-900">{{ number_format((float) $account->balance(), 2) }}</dd></div>
            </dl>

            @if ($account->description)
                <div class="mt-4 border-t border-ink-100 pt-3"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Description</h3><p class="mt-1 text-sm text-ink-700">{{ $account->description }}</p></div>
            @endif

            @if ($account->children->count())
                <div class="mt-4 border-t border-ink-100 pt-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Child Accounts</h3>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($account->children as $child)
                            <li><a href="{{ route('finance.accounts.show', $child) }}" class="text-navy-600 hover:text-navy-500">{{ $child->code }} — {{ $child->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Journal Lines</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Entry</th><th>Date</th><th>Description</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                    <tbody>
                        @forelse ($account->lines as $line)
                            <tr>
                                <td><a href="{{ route('finance.journals.show', $line->journalEntry) }}" class="font-medium text-navy-600 hover:text-navy-500">{{ $line->journalEntry->entry_number }}</a></td>
                                <td>{{ $line->journalEntry->date?->format('d M Y') }}</td>
                                <td>{{ $line->description ?? $line->journalEntry->description ?? '—' }}</td>
                                <td class="text-right text-emerald-600">{{ $line->debit > 0 ? number_format((float) $line->debit, 2) : '—' }}</td>
                                <td class="text-right text-red-600">{{ $line->credit > 0 ? number_format((float) $line->credit, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-ink-400">No journal lines for this account.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
