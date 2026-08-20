<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = Quotation::ofCompany()
            ->with('customer')
            ->withCount('items')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('quotation_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Quotation::ofCompany()->count(),
            'open' => Quotation::ofCompany()->whereIn('status', ['draft', 'sent'])->count(),
            'accepted' => Quotation::ofCompany()->where('status', 'accepted')->count(),
            'value' => (float) Quotation::ofCompany()->whereNotIn('status', ['rejected', 'expired', 'cancelled'])->sum('total'),
        ];

        return view('sales.quotations.index', compact('quotations', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', Quotation::class);

        return view('sales.quotations.form', [
            'quotation' => new Quotation,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Quotation::class);

        $data = $request->validate($this->rules());

        try {
            $quotation = DB::transaction(function () use ($data) {
                $quotation = Quotation::create([
                    'company_id' => CompanyContext::id(),
                    'quotation_number' => NumberGenerator::next('QUOT', 'quotations'),
                    'created_by' => auth()->id(),
                    'status' => 'draft',
                    ...$data,
                    ...$this->totals($data),
                ]);

                $this->syncItems($quotation, $data['items'] ?? []);

                return $quotation;
            });

            AuditLogger::created($quotation);

            return redirect()->route('sales.quotations.show', $quotation)
                ->with('success', 'Quotation created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create quotation: '.$e->getMessage());
        }
    }

    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        if ($quotation->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $quotation->load('customer', 'items.product');

        return view('sales.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorize('update', $quotation);

        if ($quotation->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $quotation->load('items');

        return view('sales.quotations.form', [
            'quotation' => $quotation,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        if ($quotation->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        // Block edits on accepted/converted quotations
        if (in_array($quotation->status, ['accepted', 'converted'])) {
            return back()->withInput()->with('error', 'Cannot edit an accepted or converted quotation.');
        }

        // Enforce valid status transitions
        $allowedTransitions = [
            'draft' => ['sent', 'rejected', 'expired'],
            'sent' => ['accepted', 'rejected', 'expired'],
            'accepted' => [],
            'rejected' => [],
            'expired' => [],
            'converted' => [],
        ];

        $newStatus = $data['status'] ?? $quotation->status;
        if (isset($allowedTransitions[$quotation->status]) && ! in_array($newStatus, $allowedTransitions[$quotation->status])) {
            return back()->withInput()->with('error', "Cannot transition from \"{$quotation->status}\" to \"{$newStatus}\".");
        }

        try {
            DB::transaction(function () use ($quotation, $data) {
                $quotation->update([...$data, ...$this->totals($data)]);
                $this->syncItems($quotation, $data['items'] ?? []);
            });
            AuditLogger::updated($quotation);
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update quotation: '.$e->getMessage());
        }

        return redirect()->route('sales.quotations.show', $quotation)
            ->with('success', 'Quotation updated.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        if ($quotation->company_id !== CompanyContext::id()) {
            abort(404);
        }

        // Cannot delete accepted/converted quotations
        if (in_array($quotation->status, ['accepted', 'converted'])) {
            return back()->with('error', 'Cannot delete an accepted or converted quotation.');
        }

        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });
        AuditLogger::deleted($quotation);

        return redirect()->route('sales.quotations.index')->with('success', 'Quotation deleted.');
    }

    public function convertToOrder(Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        if ($quotation->company_id !== CompanyContext::id()) {
            abort(404);
        }

        if ($quotation->status !== 'accepted') {
            return back()->with('error', 'Only accepted quotations can be converted to a sales order.');
        }

        $order = DB::transaction(function () use ($quotation) {
            $order = SalesOrder::create([
                'company_id' => CompanyContext::id(),
                'order_number' => NumberGenerator::next('SO', 'sales_orders'),
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'status' => 'draft',
                'created_by' => auth()->id(),
                'order_date' => now()->toDateString(),
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
                'notes' => $quotation->notes,
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                    'line_total' => $item->line_total,
                    'sort' => $item->sort,
                ]);
            }

            $quotation->update(['status' => 'converted']);

            return $order;
        });

        AuditLogger::log('convert', 'quotations', $quotation);

        return redirect()->route('sales.orders.show', $order)
            ->with('success', 'Quotation converted to sales order '.$order->order_number.'.');
    }

    public function pdf(Quotation $quotation): PDF|Response
    {
        $this->authorize('view', $quotation);

        if ($quotation->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $quotation->load('customer', 'items.product', 'createdBy');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.quotations.pdf', compact('quotation'))
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download('quotation-'.$quotation->quotation_number.'.pdf');
    }

    protected function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();
        foreach (array_values($items) as $sort => $item) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
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
        $net = $qty * $price - $discount;

        return round($net * (1 + $tax / 100), 4);
    }

    protected function totals(array $data): array
    {
        $subtotal = 0.0;
        $discount = 0.0;
        $tax = 0.0;
        foreach ($data['items'] ?? [] as $item) {
            $subtotal += (float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0);
            $discount += (float) ($item['discount'] ?? 0);
            $tax += ((float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0) - (float) ($item['discount'] ?? 0)) * ((float) ($item['tax_rate'] ?? 0) / 100);
        }

        return [
            'subtotal' => round($subtotal, 4),
            'discount' => round($discount, 4),
            'tax' => round($tax, 4),
            'total' => round($subtotal - $discount + $tax, 4),
        ];
    }

    protected function rules(): array
    {
        return [
            'customer_id' => ['required', CompanyContext::existsInCompany('customers')],
            'status' => ['required', 'in:draft,sent,accepted,rejected,expired,converted'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'currency_code' => ['nullable', 'string', 'max:8'],
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
