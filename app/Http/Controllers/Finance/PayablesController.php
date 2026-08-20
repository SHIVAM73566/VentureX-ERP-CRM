<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\LandedCost;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayablesController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $orders = PurchaseOrder::ofCompany()
            ->with('supplier')
            ->whereNotIn('status', ['cancelled'])
            ->whereRaw('total > paid_amount')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('expected_date')
            ->paginate(15)
            ->withQueryString();

        $payable = (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v;
        $due = (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->whereDate('expected_date', '<', now())->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v;
        $spend = (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->sum('total');
        $landedCosts = (float) LandedCost::ofCompany()->sum('amount');

        $agingBuckets = [
            'current' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->whereDate('expected_date', '>=', now())->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '0_30' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->whereDate('expected_date', '>=', now()->subDays(30))->whereDate('expected_date', '<', now())->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '31_60' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->whereBetween('expected_date', [now()->subDays(60), now()->subDays(31)])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '61_90' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->whereBetween('expected_date', [now()->subDays(90), now()->subDays(61)])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
            '90_plus' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->whereDate('expected_date', '<', now()->subDays(90))->selectRaw('COALESCE(SUM(total - paid_amount), 0) as v')->first()->v,
        ];

        return view('finance.payables.index', compact('orders', 'payable', 'due', 'spend', 'landedCosts', 'agingBuckets'));
    }
}
