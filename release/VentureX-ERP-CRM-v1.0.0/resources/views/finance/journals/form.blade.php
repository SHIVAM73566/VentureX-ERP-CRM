<x-layouts.app
    :title="'New Journal Entry'"
    :breadcrumbs="[['label' => 'Finance', 'url' => route('finance.journals.index')], ['label' => 'Journal Entries', 'url' => route('finance.journals.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('finance.journals.store') }}" class="space-y-6" x-data="journalForm()">
            @csrf

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Entry Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="Date *" name="date" type="date" value="{{ now()->format('Y-m-d') }}" required />
                    <x-form.input label="Description" name="description" placeholder="e.g. Freight payment to ABC Shipping" />
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Lines</h2>
                    <button type="button" @click="addLine()" class="btn-secondary">+ Add Line</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Account *</th><th>Description</th><th class="w-32">Debit</th><th class="w-32">Credit</th><th class="w-10"></th></tr></thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="index">
                                <tr>
                                    <td>
                                        <select :name="'lines['+index+'][account_id]'" x-model="line.account_id" class="input" required>
                                            <option value="">Account…</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" :name="'lines['+index+'][description]'" x-model="line.description" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'lines['+index+'][debit]'" x-model.number="line.debit" class="input" @input="clearCredit(index)" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'lines['+index+'][credit]'" x-model.number="line.credit" class="input" @input="clearDebit(index)" /></td>
                                    <td><button type="button" @click="lines.splice(index, 1)" class="text-red-500 hover:text-red-400">✕</button></td>
                                </tr>
                            </template>
                            <tr x-show="lines.length === 0"><td colspan="5" class="py-6 text-center text-ink-400">No lines yet.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col items-end gap-1 text-sm">
                    <div class="flex justify-between w-full max-w-xs"><span class="text-ink-400">Total Debits</span><span class="font-semibold text-emerald-600" x-text="totals().debit.toFixed(2)"></span></div>
                    <div class="flex justify-between w-full max-w-xs"><span class="text-ink-400">Total Credits</span><span class="font-semibold text-red-600" x-text="totals().credit.toFixed(2)"></span></div>
                    <div class="flex justify-between w-full max-w-xs border-t border-ink-200 pt-1">
                        <span class="font-semibold text-ink-700">Balance</span>
                        <span class="font-bold" :class="totals().balance === 0 ? 'text-emerald-600' : 'text-red-600'" x-text="totals().balance.toFixed(2)"></span>
                    </div>
                    <p class="text-xs text-ink-400" x-show="totals().balance !== 0">Debits must equal credits to post.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('finance.journals.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Create Draft Entry</button>
            </div>
        </form>
    </div>

    <script>
        function journalForm() {
            return {
                lines: [{ account_id: '', description: '', debit: 0, credit: 0 }],
                addLine() { this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 }); },
                clearCredit(index) { if (this.lines[index].debit > 0) this.lines[index].credit = 0; },
                clearDebit(index) { if (this.lines[index].credit > 0) this.lines[index].debit = 0; },
                totals() {
                    let debit = 0, credit = 0;
                    this.lines.forEach(l => { debit += l.debit || 0; credit += l.credit || 0; });
                    return { debit, credit, balance: +(debit - credit).toFixed(2) };
                },
            };
        }
    </script>
</x-layouts.app>
