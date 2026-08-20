<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', JournalEntry::class);

        $entries = JournalEntry::ofCompany()
            ->with('createdBy', 'lines')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('entry_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => JournalEntry::ofCompany()->count(),
            'posted' => JournalEntry::ofCompany()->where('status', 'posted')->count(),
            'draft' => JournalEntry::ofCompany()->where('status', 'draft')->count(),
        ];

        return view('finance.journals.index', compact('entries', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', JournalEntry::class);

        return view('finance.journals.form', [
            'entry' => new JournalEntry,
            'accounts' => Account::ofCompany()->where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JournalEntry::class);

        $data = $request->validate($this->rules());

        $lines = $data['lines'] ?? [];
        $debits = collect($lines)->sum('debit');
        $credits = collect($lines)->sum('credit');

        if (abs((float) $debits - (float) $credits) > 0.0001) {
            return back()->withErrors(['lines' => 'Debits must equal credits for the journal entry to balance.'])->withInput();
        }

        try {
            $entry = DB::transaction(function () use ($data, $lines) {
                $entry = JournalEntry::create([
                    'company_id' => CompanyContext::id(),
                    'entry_number' => NumberGenerator::next('JE', 'journal_entries'),
                    'created_by' => auth()->id(),
                    'date' => $data['date'],
                    'description' => $data['description'] ?? null,
                    'status' => 'draft',
                ]);

                foreach (array_values($lines) as $line) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $line['account_id'],
                        'debit' => (float) ($line['debit'] ?? 0),
                        'credit' => (float) ($line['credit'] ?? 0),
                        'description' => $line['description'] ?? null,
                    ]);
                }

                return $entry;
            });

            AuditLogger::created($entry);

            return redirect()->route('finance.journals.show', $entry)->with('success', 'Journal entry created (draft).');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create journal entry: '.$e->getMessage());
        }
    }

    public function show(JournalEntry $entry): View
    {
        $this->authorize('view', $entry);

        if ($entry->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $entry->load('lines.account', 'createdBy');

        return view('finance.journals.show', compact('entry'));
    }

    public function post(JournalEntry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);

        if ($entry->company_id !== CompanyContext::id()) {
            abort(404);
        }

        if (! $entry->isBalanced()) {
            return back()->withErrors(['entry' => 'Cannot post an unbalanced journal entry.'])->withInput();
        }

        $entry->update(['status' => 'posted']);
        AuditLogger::action($entry, 'post', 'journal_entries');

        return redirect()->route('finance.journals.show', $entry)->with('success', 'Journal entry posted.');
    }

    public function destroy(JournalEntry $entry): RedirectResponse
    {
        $this->authorize('delete', $entry);

        if ($entry->company_id !== CompanyContext::id()) {
            abort(404);
        }

        if ($entry->status === 'posted') {
            return redirect()->route('finance.journals.show', $entry)
                ->with('error', 'Posted journal entries cannot be deleted. Void them instead.');
        }

        $entry->delete();
        AuditLogger::deleted($entry);

        return redirect()->route('finance.journals.index')->with('success', 'Journal entry deleted.');
    }

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'lines' => ['sometimes', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
