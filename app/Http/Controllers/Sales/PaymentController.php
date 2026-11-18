<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\AutoJournalService;
use App\Services\CompanyContext;
use App\Services\DashboardCacheService;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::ofCompany()
            ->with('customer', 'invoice')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('payment_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->string('method')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => (float) Payment::ofCompany()->where('status', 'completed')->sum('amount'),
            'count' => Payment::ofCompany()->where('status', 'completed')->count(),
            'pending' => (float) Payment::ofCompany()->where('status', 'pending')->sum('amount'),
        ];

        return view('sales.payments.index', compact('payments', 'summary'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Payment::class);

        $presetInvoice = $request->integer('invoice_id') ? Invoice::ofCompany()->find($request->integer('invoice_id')) : null;

        return view('sales.payments.form', [
            'payment' => new Payment,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'invoices' => Invoice::ofCompany()->whereIn('status', ['sent', 'partial', 'overdue'])->orderByDesc('id')->get(),
            'presetInvoice' => $presetInvoice,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $data = $request->validate($this->rules());

        try {
            $payment = DB::transaction(function () use ($data) {
                $payment = Payment::create([
                    'company_id' => CompanyContext::id(),
                    'payment_number' => NumberGenerator::next('PAY', 'payments'),
                    'created_by' => auth()->id(),
                    ...$data,
                ]);

                $this->applyToInvoice($payment);

                return $payment;
            });

            DashboardCacheService::forget((int) $payment->company_id);
            AuditLogger::created($payment);

            if ($payment->status === 'completed') {
                AutoJournalService::recordPayment($payment);
            }

            return redirect()->route('sales.payments.index')->with('success', 'Payment recorded.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        if ($payment->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $payment->load('customer', 'invoice');

        return view('sales.payments.show', compact('payment'));
    }

    public function edit(Payment $payment): View
    {
        $this->authorize('update', $payment);

        if ($payment->company_id !== CompanyContext::id()) {
            abort(404);
        }

        return view('sales.payments.form', [
            'payment' => $payment,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'invoices' => Invoice::ofCompany()
                ->where(fn ($q) => $q->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->orWhere('id', $payment->invoice_id))
                ->orderByDesc('id')->get(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        if ($payment->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $oldInvoice = $payment->invoice;
        $oldAmount = (float) $payment->amount;
        $oldStatus = $payment->status;

        $data = $request->validate($this->rules());

        try {
            DB::transaction(function () use ($payment, $data, $oldInvoice, $oldAmount, $oldStatus) {
                $lockedPayment = Payment::ofCompany((int) $payment->company_id)
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPayment->update($data);

                if ($oldInvoice && $oldStatus === 'completed') {
                    $lockedOldInvoice = Invoice::ofCompany((int) $payment->company_id)
                        ->whereKey($oldInvoice->id)
                        ->lockForUpdate()
                        ->first();

                    if ($lockedOldInvoice) {
                        $lockedOldInvoice->paid_amount = max(0, (float) $lockedOldInvoice->paid_amount - $oldAmount);
                        $lockedOldInvoice->status = $lockedOldInvoice->paid_amount >= (float) $lockedOldInvoice->total ? 'paid' : ($lockedOldInvoice->paid_amount > 0 ? 'partial' : 'sent');
                        $lockedOldInvoice->save();
                    }
                }

                $this->applyToInvoice($lockedPayment);
            });

            $payment->refresh();
            DashboardCacheService::forget((int) $payment->company_id);
            AuditLogger::updated($payment);

            return redirect()->route('sales.payments.index')->with('success', 'Payment updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        if ($payment->company_id !== CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($payment) {
                $lockedPayment = Payment::ofCompany((int) $payment->company_id)
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPayment->status === 'completed' && $lockedPayment->invoice_id) {
                    $invoice = Invoice::ofCompany((int) $payment->company_id)
                        ->whereKey($lockedPayment->invoice_id)
                        ->lockForUpdate()
                        ->first();

                    if ($invoice) {
                        $invoice->paid_amount = max(0, (float) $invoice->paid_amount - (float) $lockedPayment->amount);
                        $invoice->status = $invoice->paid_amount >= (float) $invoice->total ? 'paid' : ($invoice->paid_amount > 0 ? 'partial' : 'sent');
                        $invoice->save();
                    }
                }

                $lockedPayment->delete();
            });

            DashboardCacheService::forget((int) $payment->company_id);
            AuditLogger::deleted($payment);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to delete payment.');
        }

        return redirect()->route('sales.payments.index')->with('success', 'Payment deleted.');
    }

    protected function applyToInvoice(Payment $payment): void
    {
        if (! $payment->invoice_id || $payment->status !== 'completed') {
            return;
        }

        // Callers invoke this inside a transaction; lock the invoice row so
        // two concurrent payments cannot overwrite each other's paid_amount.
        $invoice = Invoice::ofCompany((int) $payment->company_id)
            ->whereKey($payment->invoice_id)
            ->lockForUpdate()
            ->first();

        if (! $invoice) {
            return;
        }

        $invoice->paid_amount = min((float) $invoice->total, (float) $invoice->paid_amount + (float) $payment->amount);
        $invoice->status = $invoice->paid_amount >= (float) $invoice->total ? 'paid' : 'partial';
        $invoice->save();
    }

    protected function rules(): array
    {
        return [
            'customer_id' => ['required', CompanyContext::existsInCompany('customers')],
            'invoice_id' => ['nullable', CompanyContext::existsInCompany('invoices')],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,bank,cheque,card,upi,other'],
            'reference' => ['nullable', 'string', 'max:128'],
            'status' => ['required', 'in:pending,completed,failed,refunded'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
