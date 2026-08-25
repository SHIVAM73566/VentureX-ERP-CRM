<x-layouts.app
    :title="'Journal Entry — '.($entry->entry_number ?? 'New')"
    :breadcrumbs="[['label' => 'Finance', 'url' => route('finance.journals.index')], ['label' => 'Journal Entries', 'url' => route('finance.journals.index')], ['label' => $entry->entry_number]]">

    <x-slot name="actions">
        @can('update', $entry)
            @if ($entry->status !== 'posted')
                <form method="POST" action="{{ route('finance.journals.post', $entry) }}" onsubmit="return confirm('Post this journal entry? This cannot be undone.')">
                    @csrf
                    <button type="submit" class="btn-accent">Post Entry</button>
                </form>
            @endif
        @endcan
        @can('delete', $entry)
            @if ($entry->status !== 'posted')
                <form method="POST" action="{{ route('finance.journals.destroy', $entry) }}" onsubmit="return confirm('Delete this journal entry?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
                </form>
            @endif
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Entry Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd>{{ $entry->entry_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Date</dt><dd>{{ $entry->date?->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd><span class="badge-{{ $entry->status === 'posted' ? 'green' : 'amber' }}">{{ \App\Models\JournalEntry::STATUSES[$entry->status] ?? $entry->status }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Created By</dt><dd>{{ $entry->createdBy?->name ?? '—' }}</dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Lines</dt><dd class="font-bold text-ink-900">{{ $entry->lines->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Total Debits</dt><dd class="font-semibold text-emerald-600">{{ number_format((float) $entry->lines->sum('debit'), 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Total Credits</dt><dd class="font-semibold text-red-600">{{ number_format((float) $entry->lines->sum('credit'), 2) }}</dd></div>
            </dl>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Lines</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Account</th><th>Description</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                    <tbody>
                        @forelse ($entry->lines as $line)
                            <tr>
                                <td>
                                    <a href="{{ route('finance.accounts.show', $line->account) }}" class="font-semibold text-navy-600 hover:text-navy-500">{{ $line->account?->code }} — {{ $line->account?->name }}</a>
                                </td>
                                <td>{{ $line->description ?? '—' }}</td>
                                <td class="text-right text-emerald-600">{{ $line->debit > 0 ? number_format((float) $line->debit, 2) : '—' }}</td>
                                <td class="text-right text-red-600">{{ $line->credit > 0 ? number_format((float) $line->credit, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">No lines.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
