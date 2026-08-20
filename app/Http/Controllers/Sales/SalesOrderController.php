<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $orders = SalesOrder::ofCompany()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => SalesOrder::ofCompany()->count(),
            'open' => SalesOrder::ofCompany()->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'value' => (float) SalesOrder::ofCompany()->whereNotIn('status', ['cancelled'])->sum('total'),
            'outstanding' => (float) SalesOrder::ofCompany()->whereNotIn('status', ['cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as outstanding')->first()->outstanding,
        ];

        return view('sales.orders.index', compact('orders', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', SalesOrder::class);

        return view('sales.orders.form', [
            'order' => new SalesOrder,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'quotations' => Quotation::ofCompany()->where('status', 'accepted')->orderByDesc('id')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        $data = $request->validate($this->rules());
        $data['status'] = 'draft';

        try {
            $order = DB::transaction(function () use ($data) {
                $order = SalesOrder::create([
                    'company_id' => CompanyContext::id(),
                    'order_number' => NumberGenerator::next('SO', 'sales_orders'),
                    'created_by' => auth()->id(),
                    ...$data,
                    ...$this->totals($data),
                ]);

                $this->syncItems($order, $data['items'] ?? []);

                return $order;
            });

            AuditLogger::created($order);

            return redirect()->route('sales.orders.show', $order)->with('success', 'Sales order created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create sales order: '.$e->getMessage());
        }
    }

    public function show(SalesOrder $order): View
    {
        $this->authorize('view', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $order->load('customer', 'quotation', 'items.product', 'invoices');

        return view('sales.orders.show', compact('order'));
    }

    public function edit(SalesOrder $order): View
    {
        $this->authorize('update', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $order->load('items');

        return view('sales.orders.form', [
            'order' => $order,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'quotations' => Quotation::ofCompany()->where('status', 'accepted')->orderByDesc('id')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SalesOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        $oldStatus = $order->status;

        // Block financial edits on completed orders
        if (in_array($order->status, ['completed', 'shipped'])) {
            $allowed = ['notes' => $data['notes'] ?? $order->notes];
            $order->update($allowed);
            AuditLogger::updated($order);

            return redirect()->route('sales.orders.show', $order)->with('success', 'Order notes updated.');
        }

        $allowedTransitions = [
            'draft' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['completed'],
            'completed' => [],
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
                if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
                    StockService::recordSaleItems($order);
                } elseif ($newStatus === 'cancelled' && $oldStatus !== 'cancelled' && in_array($oldStatus, ['confirmed', 'processing'])) {
                    StockService::reverseSaleItems($order);
                }
            });
            AuditLogger::updated($order);
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update sales order: '.$e->getMessage());
        }

        return redirect()->route('sales.orders.show', $order)->with('success', 'Sales order updated.');
    }

    public function destroy(SalesOrder $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        if ($order->company_id !== CompanyContext::id()) {
            abort(404);
        }

        // Cannot delete completed, shipped, or orders with linked invoices
        if (in_array($order->status, ['completed', 'shipped'])) {
            return back()->with('error', 'Cannot delete a completed or shipped order.');
        }

        if ($order->invoices()->count() > 0) {
            return back()->with('error', 'Cannot delete an order with linked invoices.');
        }

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });
        AuditLogger::deleted($order);

        return redirect()->route('sales.orders.index')->with('success', 'Sales order deleted.');
    }

    protected function syncItems(SalesOrder $order, array $items): void
    {
        $order->items()->delete();
        foreach (array_values($items) as $sort => $item) {
            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'discount' => (float) ($item['discount'] ?? 0),
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
        $discount = (float) ($item['discount'] ?? 0);
        $tax = (float) ($item['tax_rate'] ?? 0);

        return round(($qty * $price - $discount) * (1 + $tax / 100), 4);
    }

    protected function totals(array $data): array
    {
        $subtotal = 0.0;
        $discount = 0.0;
        $tax = 0.0;
        $shipping = (float) ($data['shipping'] ?? 0);
        foreach ($data['items'] ?? [] as $item) {
            $subtotal += (float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0);
            $discount += (float) ($item['discount'] ?? 0);
            $tax += ((float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0) - (float) ($item['discount'] ?? 0)) * ((float) ($item['tax_rate'] ?? 0) / 100);
        }

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
            'customer_id' => ['required', CompanyContext::existsInCompany('customers')],
            'quotation_id' => ['nullable', CompanyContext::existsInCompany('quotations')],
            'status' => ['required', 'in:draft,confirmed,processing,shipped,completed,cancelled'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['nullable', CompanyContext::existsInCompany('products')],
            'items.*.description' => ['required', 'string', 'max:512'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
