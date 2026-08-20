<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceivablesController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::ofCompany()
            ->with('customer', 'payments')
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            })
            ->when($request->filled('aging'), function ($q) use ($request) {
                $days = (int) $request->string('aging');
                if ($days > 0) {
                    $q->whereDate('due_date', '<', now()->subDays($days));
                }
            })
            ->orderBy('due_date')
            ->paginate(15)
            ->withQueryString();

        $outstanding = (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v;
        $overdue = (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->whereDate('due_date', '<', now())->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v;
        $collected = (float) Payment::ofCompany()->where('status', 'completed')->sum('amount');

        $agingBuckets = [
            'current' => (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->whereDate('due_date', '>=', now())->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '0_30' => (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->whereDate('due_date', '>=', now()->subDays(30))->whereDate('due_date', '<', now())->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '31_60' => (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '61_90' => (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->whereBetween('due_date', [now()->subDays(90), now()->subDays(61)])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '90_plus' => (float) Invoice::ofCompany()->whereNotIn('status', ['paid', 'cancelled'])->whereDate('due_date', '<', now()->subDays(90))->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
        ];

        return view('finance.receivables.index', compact('invoices', 'outstanding', 'overdue', 'collected', 'agingBuckets'));
    }
}
