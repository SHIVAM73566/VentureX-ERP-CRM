<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\AuditLogger;
use App\Services\AutoJournalService;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::ofCompany()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Invoice::ofCompany()->count(),
            'open' => Invoice::ofCompany()->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])->count(),
            'value' => (float) Invoice::ofCompany()->whereNotIn('status', ['cancelled'])->sum('total'),
            'outstanding' => (float) Invoice::ofCompany()->whereNotIn('status', ['cancelled'])->selectRaw('COALESCE(SUM(total - paid_amount), 0) as outstanding')->value('outstanding'),
        ];

        return view('sales.invoices.index', compact('invoices', 'summary'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        $presetOrder = $request->integer('sales_order_id') ? SalesOrder::ofCompany()->find($request->integer('sales_order_id')) : null;

        return view('sales.invoices.form', [
            'invoice' => new Invoice,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'orders' => SalesOrder::ofCompany()->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])->orderByDesc('id')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
            'presetOrder' => $presetOrder,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $request->validate($this->rules());
        $data['status'] = 'draft';

        try {
            $invoice = DB::transaction(function () use ($data) {
                $invoice = Invoice::create([
                    'company_id' => CompanyContext::id(),
                    'invoice_number' => NumberGenerator::next('INV', 'invoices'),
                    'created_by' => auth()->id(),
                    ...$data,
                    ...$this->totals($data),
                ]);

                $this->syncItems($invoice, $data['items'] ?? []);
                AutoJournalService::recordInvoice($invoice);

                return $invoice;
            });

            AuditLogger::created($invoice);

            return redirect()->route('sales.invoices.show', $invoice)->with('success', 'Invoice created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create invoice: '.$e->getMessage());
        }
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        if ($invoice->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $invoice->load('customer', 'salesOrder', 'items.product', 'payments');

        return view('sales.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        if ($invoice->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $invoice->load('items');

        return view('sales.invoices.form', [
            'invoice' => $invoice,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'orders' => SalesOrder::ofCompany()->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])->orderByDesc('id')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if ($invoice->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        // Block financial edits on paid invoices — only notes allowed
        if ($invoice->status === 'paid') {
            $allowed = ['notes' => $data['notes'] ?? $invoice->notes];
            $invoice->update($allowed);
            AuditLogger::updated($invoice);

            return redirect()->route('sales.invoices.show', $invoice)->with('success', 'Invoice notes updated.');
        }

        // Paid status can ONLY be set through PaymentController, never via edit
        $allowedTransitions = [
            'draft' => ['sent', 'cancelled'],
            'sent' => ['partial', 'overdue', 'cancelled'],
            'partial' => ['overdue', 'cancelled'],
            'overdue' => ['cancelled'],
            'paid' => [],
        ];

        $newStatus = $data['status'] ?? $invoice->status;
        if (isset($allowedTransitions[$invoice->status]) && ! in_array($newStatus, $allowedTransitions[$invoice->status])) {
            return back()->withInput()->with('error', "Cannot transition from \"{$invoice->status}\" to \"{$newStatus}\".");
        }

        DB::transaction(function () use ($invoice, $data) {
            $invoice->update([...$data, ...$this->totals($data)]);
            $this->syncItems($invoice, $data['items'] ?? []);
        });
        AuditLogger::updated($invoice);

        return redirect()->route('sales.invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        if ($invoice->company_id !== CompanyContext::id()) {
            abort(404);
        }

        // Cannot delete paid or partially paid invoices
        if (in_array($invoice->status, ['paid', 'partial'])) {
            return back()->with('error', 'Cannot delete a paid or partially paid invoice.');
        }

        // Cannot delete invoice with linked payments
        if ($invoice->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete an invoice with recorded payments.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });
        AuditLogger::deleted($invoice);

        return redirect()->route('sales.invoices.index')->with('success', 'Invoice deleted.');
    }

    public function pdf(Invoice $invoice): PDF|Response
    {
        $this->authorize('view', $invoice);

        if ($invoice->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $invoice->load('customer', 'salesOrder', 'items.product', 'payments', 'createdBy');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.invoices.pdf', compact('invoice'))
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download('invoice-'.$invoice->invoice_number.'.pdf');
    }

    protected function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        foreach (array_values($items) as $sort => $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
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
            'sales_order_id' => ['nullable', CompanyContext::existsInCompany('sales_orders')],
            'status' => ['required', 'in:draft,sent,partial,overdue,cancelled'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
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
