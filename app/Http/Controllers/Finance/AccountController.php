<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Account::class);

        $accounts = Account::ofCompany()
            ->with('parent', 'children')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        $totals = collect(Account::TYPES)->mapWithKeys(function ($label, $type) {
            return [$type => Account::ofCompany()->where('type', $type)->count()];
        });

        return view('finance.accounts.index', compact('accounts', 'totals'));
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        return view('finance.accounts.form', [
            'account' => new Account,
            'parents' => Account::ofCompany()->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Account::class);

        try {
            $account = DB::transaction(function () use ($request) {
                return Account::create([
                    'company_id' => CompanyContext::id(),
                    ...$request->validate($this->rules()),
                ]);
            });

            AuditLogger::created($account);

            return redirect()->route('finance.accounts.index')->with('success', 'Account created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create account: '.$e->getMessage());
        }
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);

        if ((int) $account->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $account->load('parent', 'children', 'lines.journalEntry');

        return view('finance.accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        if ((int) $account->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('finance.accounts.form', [
            'account' => $account,
            'parents' => Account::ofCompany()->where('id', '!=', $account->id)->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        if ((int) $account->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $account) {
                $account->update($request->validate($this->rules()));
            });
            AuditLogger::updated($account);

            return redirect()->route('finance.accounts.index')->with('success', 'Account updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update account: '.$e->getMessage());
        }
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        if ((int) $account->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        if ($account->lines()->exists() || $account->children()->exists()) {
            return redirect()->route('finance.accounts.index')
                ->with('error', 'Cannot delete an account that has journal lines or child accounts.');
        }

        $account->delete();
        AuditLogger::deleted($account);

        return redirect()->route('finance.accounts.index')->with('success', 'Account deleted.');
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,income,expense'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
