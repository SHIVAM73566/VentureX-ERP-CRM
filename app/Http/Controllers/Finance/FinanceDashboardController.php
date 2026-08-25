<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LandedCost;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Services\CompanyContext;
use Illuminate\View\View;

class FinanceDashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Account::class);

        $totalReceivable = (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->value('v');
        $totalPayable = (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->value('v');
        $cashReceived = (float) Payment::ofCompany()->where('status', 'completed')->sum('amount');
        $landedCosts = (float) LandedCost::ofCompany()->sum('amount');

        $assets = $this->balanceFor(['asset']);
        $liabilities = $this->balanceFor(['liability']);
        $income = $this->balanceFor(['income']);
        $expenses = $this->balanceFor(['expense']);
        $equity = $this->balanceFor(['equity']);

        $recentEntries = JournalEntry::ofCompany()
            ->with('createdBy')
            ->latest()
            ->limit(10)
            ->get();

        $topAccounts = Account::ofCompany()
            ->withCount('lines')
            ->orderByDesc('lines_count')
            ->limit(8)
            ->get();

        $monthly = Invoice::ofCompany()
            ->selectRaw('SUBSTR(issue_date, 1, 7) as month, SUM(total) as value')
            ->whereNotIn('status', ['cancelled'])
            ->where('issue_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('value', 'month');

        return view('finance.dashboard', compact(
            'totalReceivable', 'totalPayable', 'cashReceived', 'landedCosts',
            'assets', 'liabilities', 'income', 'expenses', 'equity',
            'recentEntries', 'topAccounts', 'monthly',
        ));
    }

    protected function balanceFor(array $types): float
    {
        return (float) JournalEntryLine::query()
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('chart_of_accounts.company_id', CompanyContext::id())
            ->whereIn('chart_of_accounts.type', $types)
            ->selectRaw("
                SUM(CASE WHEN chart_of_accounts.type IN ('asset', 'expense') THEN journal_entry_lines.debit - journal_entry_lines.credit ELSE journal_entry_lines.credit - journal_entry_lines.debit END) as balance
            ")
            ->value('balance') ?? 0;
    }
}
