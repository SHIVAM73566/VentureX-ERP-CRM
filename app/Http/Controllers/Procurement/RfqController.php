<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\RfqResponse;
use App\Models\Supplier;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RfqController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Rfq::class);

        $rfqs = Rfq::ofCompany()
            ->with('items', 'responses')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('rfq_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Rfq::ofCompany()->count(),
            'open' => Rfq::ofCompany()->where('status', 'open')->count(),
            'awarded' => Rfq::ofCompany()->where('status', 'awarded')->count(),
            'responses' => (int) Rfq::ofCompany()->withCount('responses')->get()->sum('responses_count'),
        ];

        return view('procurement.rfqs.index', compact('rfqs', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', Rfq::class);

        return view('procurement.rfqs.form', [
            'rfq' => new Rfq,
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Rfq::class);

        $data = $request->validate($this->rules());

        try {
            $rfq = DB::transaction(function () use ($data) {
                $rfq = Rfq::create([
                    'company_id' => CompanyContext::id(),
                    'rfq_number' => NumberGenerator::next('RFQ', 'rfqs'),
                    'created_by' => auth()->id(),
                    'status' => 'draft',
                    ...$data,
                ]);

                $this->syncItems($rfq, $data['items'] ?? []);

                foreach ($data['supplier_ids'] ?? [] as $supplierId) {
                    RfqResponse::create([
                        'rfq_id' => $rfq->id,
                        'supplier_id' => $supplierId,
                        'status' => 'submitted',
                    ]);
                }

                return $rfq;
            });

            AuditLogger::created($rfq);

            return redirect()->route('procurement.rfqs.show', $rfq)->with('success', 'RFQ created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create RFQ: '.$e->getMessage());
        }
    }

    public function show(Rfq $rfq): View
    {
        $this->authorize('view', $rfq);

        if ((int) $rfq->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $rfq->load('items.product', 'responses.supplier', 'createdBy');

        return view('procurement.rfqs.show', compact('rfq'));
    }

    public function edit(Rfq $rfq): View
    {
        $this->authorize('update', $rfq);

        if ((int) $rfq->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $rfq->load('items');

        return view('procurement.rfqs.form', [
            'rfq' => $rfq,
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Rfq $rfq): RedirectResponse
    {
        $this->authorize('update', $rfq);

        if ((int) $rfq->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        try {
            DB::transaction(function () use ($data, $rfq) {
                $rfq->update($data);
                $this->syncItems($rfq, $data['items'] ?? []);
            });
            AuditLogger::updated($rfq);

            return redirect()->route('procurement.rfqs.show', $rfq)->with('success', 'RFQ updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update RFQ: '.$e->getMessage());
        }
    }

    public function destroy(Rfq $rfq): RedirectResponse
    {
        $this->authorize('delete', $rfq);

        if ((int) $rfq->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $rfq->delete();
        AuditLogger::deleted($rfq);

        return redirect()->route('procurement.rfqs.index')->with('success', 'RFQ deleted.');
    }

    public function createOrder(Rfq $rfq, Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        // IDOR protection: ensure RFQ belongs to current user's company
        if ((int) $rfq->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'po_number' => ['nullable', 'string', 'max:32'],
        ]);

        $supplier = Supplier::ofCompany()->findOrFail($data['supplier_id']);

        $order = PurchaseOrder::create([
            'company_id' => CompanyContext::id(),
            'po_number' => $data['po_number'] ?? 'PO-'.strtoupper(date('Ymd')).'-'.str_pad((string) (PurchaseOrder::ofCompany()->count() + 1), 4, '0', STR_PAD_LEFT),
            'supplier_id' => $supplier->id,
            'rfq_id' => $rfq->id,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'order_date' => now()->toDateString(),
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0,
            'created_by' => auth()->id(),
        ]);

        foreach ($rfq->items as $rfqItem) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $rfqItem->product_id,
                'description' => $rfqItem->description,
                'quantity' => $rfqItem->quantity,
                'unit' => $rfqItem->unit,
                'unit_price' => 0,
                'tax_rate' => 0,
                'line_total' => 0,
            ]);
        }

        $rfq->update(['status' => 'awarded']);
        $rfq->responses()->where('supplier_id', $supplier->id)->update(['status' => 'awarded']);

        AuditLogger::action($order, 'create', 'purchase_orders');

        return redirect()->route('procurement.orders.edit', $order)->with('success', 'Purchase order created from RFQ. Set unit prices to complete.');
    }

    protected function syncItems(Rfq $rfq, array $items): void
    {
        $rfq->items()->delete();
        foreach (array_values($items) as $sort => $item) {
            RfqItem::create([
                'rfq_id' => $rfq->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit' => $item['unit'] ?? null,
                'sort' => $sort,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,sent,open,awarded,cancelled'],
            'issued_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['integer', CompanyContext::existsInCompany('suppliers')],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['nullable', CompanyContext::existsInCompany('products')],
            'items.*.description' => ['required', 'string', 'max:512'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit' => ['nullable', 'string', 'max:24'],
        ];
    }
}
