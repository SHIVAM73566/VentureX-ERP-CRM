<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Rfq;
use App\Models\Supplier;
use App\Services\AuditLogger;
use App\Services\AutoJournalService;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $orders = PurchaseOrder::ofCompany()
            ->with('supplier')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => PurchaseOrder::ofCompany()->count(),
            'open' => PurchaseOrder::ofCompany()->whereIn('status', ['draft', 'pending', 'approved', 'ordered', 'partially_received'])->count(),
            'value' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->sum('total'),
            'payable' => (float) PurchaseOrder::ofCompany()->whereNotIn('status', ['cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as payable')->value('payable'),
        ];

        return view('procurement.orders.index', compact('orders', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', PurchaseOrder::class);

        return view('procurement.orders.form', [
            'order' => new PurchaseOrder,
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
            'rfqs' => Rfq::ofCompany()->whereIn('status', ['open', 'awarded'])->orderByDesc('id')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $data = $request->validate($this->rules());
        $data['status'] = 'draft';

        try {
            $order = DB::transaction(function () use ($data) {
                $order = PurchaseOrder::create([
                    'company_id' => CompanyContext::id(),
                    'po_number' => NumberGenerator::next('PO', 'purchase_orders'),
                    'created_by' => auth()->id(),
                    ...$data,
                    ...$this->totals($data),
                ]);

                $this->syncItems($order, $data['items'] ?? []);

                return $order;
            });

            AuditLogger::created($order);

            return redirect()->route('procurement.orders.show', $order)->with('success', 'Purchase order created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create purchase order: '.$e->getMessage());
        }
    }

    public function show(PurchaseOrder $order): View
    {
        $this->authorize('view', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $order->load('supplier', 'rfq', 'items.product', 'createdBy');

        return view('procurement.orders.show', compact('order'));
    }

    public function edit(PurchaseOrder $order): View
    {
        $this->authorize('update', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $order->load('items');

        return view('procurement.orders.form', [
            'order' => $order,
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
            'rfqs' => Rfq::ofCompany()->whereIn('status', ['open', 'awarded'])->orderByDesc('id')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        $oldStatus = $order->status;

        // Block financial edits on received orders
        if (in_array($order->status, ['received'])) {
            $allowed = ['notes' => $data['notes'] ?? $order->notes];
            $order->update($allowed);
            AuditLogger::updated($order);

            return redirect()->route('procurement.orders.show', $order)->with('success', 'Order notes updated.');
        }

        $allowedTransitions = [
            'draft' => ['pending', 'cancelled'],
            'pending' => ['approved', 'cancelled'],
            'approved' => ['ordered', 'cancelled'],
            'ordered' => ['partially_received', 'received', 'cancelled'],
            'partially_received' => ['received', 'cancelled'],
        ];

        $newStatus = $data['status'] ?? $order->status;
        if (isset($allowedTransitions[$order->status]) && ! in_array($newStatus, $allowedTransitions[$order->status])) {
            return back()->withInput()->with('error', "Cannot transition from \"{$order->status}\" to \"{$newStatus}\".");
        }

        try {
            DB::transaction(function () use ($order, $data, $oldStatus) {
                $order->update([...$data, ...$this->totals($data)]);
                $this->syncItems($order, $data['items'] ?? []);

                $newStatus = $data['status'] ?? $order->status;
                if ($newStatus === 'received' && $oldStatus !== 'received') {
                    StockService::recordPurchaseReceipt($order);
                    AutoJournalService::recordPurchaseOrder($order);
                } elseif ($newStatus !== 'received' && $oldStatus === 'received') {
                    StockService::reversePurchaseReceipt($order);
                }
            });
            AuditLogger::updated($order);
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update purchase order: '.$e->getMessage());
        }

        return redirect()->route('procurement.orders.show', $order)->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        // Cannot delete ordered/received/partially_received orders
        if (in_array($order->status, ['ordered', 'partially_received', 'received'])) {
            return back()->with('error', 'Cannot delete an order that has been placed or received.');
        }

        if ($order->paid_amount > 0) {
            return back()->with('error', 'Cannot delete a purchase order with recorded payments.');
        }

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });
        AuditLogger::deleted($order);

        return redirect()->route('procurement.orders.index')->with('success', 'Purchase order deleted.');
    }

    protected function syncItems(PurchaseOrder $order, array $items): void
    {
        $order->items()->delete();
        foreach (array_values($items) as $sort => $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit' => $item['unit'] ?? null,
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'tax_rate' => (float) ($item['tax_rate'] ?? 0),
                'line_total' => $this->lineTotal($item),
                'sort' => $sort,
            ]);
        }
    }

    protected function lineTotal(array $item): float
    {
        $qty = (float) ($item['quantity'] ?? 1);
        $price = (float) ($item['unit_price'] ?? 0);
        $tax = (float) ($item['tax_rate'] ?? 0);

        return round($qty * $price * (1 + $tax / 100), 4);
    }

    protected function totals(array $data): array
    {
        $subtotal = 0.0;
        $tax = 0.0;
        foreach ($data['items'] ?? [] as $item) {
            $subtotal += (float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0);
            $tax += (float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0) * ((float) ($item['tax_rate'] ?? 0) / 100);
        }

        $discount = (float) ($data['discount'] ?? 0);
        $shipping = (float) ($data['shipping'] ?? 0);

        return [
            'subtotal' => round($subtotal, 4),
            'discount' => round($discount, 4),
            'tax' => round($tax, 4),
            'shipping' => round($shipping, 4),
            'total' => round($subtotal - $discount + $tax + $shipping, 4),
        ];
    }

    protected function rules(): array
    {
        return [
            'supplier_id' => ['required', CompanyContext::existsInCompany('suppliers')],
            'rfq_id' => ['nullable', CompanyContext::existsInCompany('rfqs')],
            'status' => ['required', 'in:draft,pending,approved,ordered,partially_received,received,cancelled'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'payment_status' => ['sometimes', 'in:unpaid,partial,paid'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['nullable', CompanyContext::existsInCompany('products')],
            'items.*.description' => ['required', 'string', 'max:512'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit' => ['nullable', 'string', 'max:24'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
