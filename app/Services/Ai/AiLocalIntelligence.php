<?php

namespace App\Services\Ai;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\DB;

/**
 * Local (database + rule) business intelligence. Runs BEFORE any AI call and
 * answers the majority of common business questions instantly and for free.
 */
class AiLocalIntelligence
{
    /**
     * Attempt to answer a natural-language business question using local data.
     * Returns null when the question needs AI interpretation instead.
     */
    public function answer(string $question, ?int $companyId = null): ?string
    {
        $q = mb_strtolower(trim($question));

        if (preg_match('/(overdue invoice|invoice(s)? overdue|unpaid invoice)/i', $q)) {
            return $this->overdueInvoices($companyId);
        }

        if (preg_match('/receivable|who ow(e|es).*(money|payment)|outstanding.*(amount|receivable)/i', $q)) {
            return $this->receivables($companyId);
        }

        if (preg_match('/payable|we ow(e|es)/i', $q)) {
            return $this->payables($companyId);
        }

        if (preg_match('/(low stock|below reorder|reorder|restock|slow moving|need.*order|needs? reorder)/i', $q)) {
            return $this->lowStock($companyId);
        }

        if (preg_match('/(lead|contact|follow.?up).*(today|soon|due|now)/i', $q)) {
            return $this->leadsDue($companyId);
        }

        if (preg_match('/(best|top|strongest).*(opportunit|pipeline)/i', $q)) {
            return $this->topOpportunities($companyId);
        }

        if (preg_match('/(red.?flag|risk(y|ier)?|high.?risk).*(offer|supplier)/i', $q)) {
            return $this->redFlagOffers($companyId);
        }

        if (preg_match('/(open|pending).*purchase order|po.*(need|require|attention)/i', $q)) {
            return $this->purchaseOrdersAttention($companyId);
        }

        if (preg_match('/customer.*(overdue|owe|not contact|uncontacted)/i', $q)) {
            return $this->customersOwing($companyId);
        }

        return null;
    }

    public function overdueInvoices(?int $companyId = null): string
    {
        $invoices = Invoice::ofCompany($companyId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->get();

        $total = $invoices->sum(fn ($i) => (float) $i->outstandingBalance());

        return $this->render('Overdue invoices', $invoices->count(), $total,
            $invoices->sortByDesc(fn ($i) => (float) $i->outstandingBalance())->take(5)
                ->map(fn ($i) => "{$i->invoice_number} — {$i->customer?->name} — outstanding {$i->outstandingBalance()} (due {$i->due_date->format('Y-m-d')})")
                ->all());
    }

    public function receivables(?int $companyId = null): string
    {
        $invoices = Invoice::ofCompany($companyId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->get();

        $total = $invoices->sum(fn ($i) => (float) $i->outstandingBalance());

        return $this->render('Open receivables', $invoices->count(), $total,
            $invoices->sortByDesc(fn ($i) => (float) $i->outstandingBalance())->take(5)
                ->map(fn ($i) => "{$i->invoice_number} — {$i->customer?->name} — outstanding {$i->outstandingBalance()}")
                ->all());
    }

    public function payables(?int $companyId = null): string
    {
        $count = DB::table('purchase_orders')
            ->where('company_id', $companyId ?? CompanyContext::id())
            ->whereIn('status', ['approved', 'sent', 'open'])
            ->count();

        return "[FACT]\n{$count} purchase orders are open or approved.\n"
            ."[CALCULATION]\nPayables depend on approved POs; values are tracked in the finance module.\n"
            ."[RECOMMENDATION]\nReview open purchase orders in Procurement before scheduling payments.";
    }

    public function lowStock(?int $companyId = null): string
    {
        $low = Product::ofCompany($companyId)
            ->where('reorder_level', '>', 0)
            ->get()
            ->filter(fn ($p) => $p->isLowStock())
            ->values();

        $count = $low->count();

        if ($count === 0) {
            return "[FACT]\nNo products are currently below their reorder level.";
        }

        return $this->render('Products below reorder level', $count, null,
            $low->take(10)->map(fn ($p) => "{$p->name} — available {$p->availableStock()} / reorder level {$p->reorder_level}")->all());
    }

    public function leadsDue(?int $companyId = null): string
    {
        $leads = Lead::ofCompany($companyId)
            ->whereNotNull('next_follow_up')
            ->where('status', '!=', 'won')
            ->where('next_follow_up', '<=', now()->addDay())
            ->orderBy('next_follow_up')
            ->limit(8)
            ->get();

        if ($leads->isEmpty()) {
            return "[FACT]\nNo leads are due for follow-up in the next 24 hours.";
        }

        return "[FACT]\n{$leads->count()} leads need follow-up.\n".$leads
            ->map(fn ($l) => "{$l->contact_name} ({$l->company_name}) — due {$l->next_follow_up->format('Y-m-d')}")
            ->implode("\n")
            ."\n[RECOMMENDATION]\nContact the overdue leads first.";
    }

    public function topOpportunities(?int $companyId = null): string
    {
        $top = Opportunity::ofCompany($companyId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->orderByDesc('expected_value')
            ->limit(5)
            ->get();

        if ($top->isEmpty()) {
            return "[FACT]\nThere are no open opportunities.";
        }

        $total = $top->sum('expected_value');

        return "[FACT]\nTop open opportunities:\n".$top
            ->map(fn ($o) => "{$o->title} — {$o->stage} — value {$o->expected_value}")
            ->implode("\n")
            ."\n[CALCULATION]\nCombined value of top 5: {$total}\n[RECOMMENDATION]\nFocus effort on the highest-value opportunity closest to won.";
    }

    public function redFlagOffers(?int $companyId = null): string
    {
        $red = SupplierOffer::ofCompany($companyId)
            ->with('supplier')
            ->where(function ($q) {
                $q->where('quality_status', 'RED')
                    ->orWhere('risk_level', 'HIGH');
            })
            ->latest()
            ->limit(8)
            ->get();

        if ($red->isEmpty()) {
            return "[FACT]\nNo supplier offers are currently red-flagged or high-risk.";
        }

        return "[FACT]\n{$red->count()} supplier offers need attention:\n".$red
            ->map(fn ($o) => "{$o->supplier?->name} — {$o->material_description} — quality {$o->quality_status} — risk {$o->risk_level}")
            ->implode("\n")
            ."\n[RECOMMENDATION]\nRequire human review before working with red-flagged suppliers.";
    }

    public function purchaseOrdersAttention(?int $companyId = null): string
    {
        $count = PurchaseOrder::ofCompany($companyId)
            ->whereIn('status', ['draft', 'pending_approval'])
            ->count();

        return $count > 0
            ? "[FACT]\n{$count} purchase orders are in draft or pending approval.\n[RECOMMENDATION]\nReview them to avoid procurement delays."
            : "[FACT]\nNo purchase orders currently require approval.";
    }

    public function customersOwing(?int $companyId = null): string
    {
        $rows = Invoice::ofCompany($companyId)
            ->with('customer')
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->get()
            ->groupBy('customer_id');

        if ($rows->isEmpty()) {
            return "[FACT]\nNo customers currently have open invoices.";
        }

        $lines = [];
        foreach ($rows as $customerId => $invoices) {
            $customer = $invoices->first()->customer;
            $total = $invoices->sum(fn ($i) => (float) $i->outstandingBalance());
            $lines[] = "{$customer?->name} — outstanding {$total}";
        }

        return "[FACT]\n".$rows->count()." customers have open balances:\n".implode("\n", array_slice($lines, 0, 8));
    }

    public function customerSummary(Customer $customer): string
    {
        $totalInvoices = $customer->invoices()->count();
        $openInvoices = $customer->invoices()->whereIn('status', ['unpaid', 'partial', 'overdue'])->count();
        $totalRevenue = (float) $customer->invoices()->where('status', 'paid')->sum('total');
        $outstanding = (float) $customer->invoices()->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('total');
        $totalOrders = $customer->salesOrders()->count();
        $totalQuotations = $customer->quotations()->count();
        $contactCount = $customer->contacts()->count();

        $out = "[FACT]\nCustomer: {$customer->name}\n"
            ."Status: {$customer->status}\n"
            ."Total invoices: {$totalInvoices} (open: {$openInvoices})\n"
            ."Total revenue (paid): {$totalRevenue}\n"
            ."Outstanding balance: {$outstanding}\n"
            ."Sales orders: {$totalOrders}\n"
            ."Quotations: {$totalQuotations}\n"
            ."Contacts: {$contactCount}";

        if ($outstanding > 0) {
            $out .= "\n[RECOMMENDATION]\nOutstanding balance detected. Consider following up for payment collection.";
        }

        return $out;
    }

    public function leadSummary(Lead $lead): string
    {
        $out = "[FACT]\nLead: {$lead->contact_name}\n"
            ."Company: {$lead->company_name}\n"
            ."Status: {$lead->status}\n"
            ."Source: {$lead->source}\n"
            ."Value: {$lead->expected_value}\n"
            ."Stage: {$lead->stage}";

        if ($lead->next_follow_up) {
            $out .= "\nNext follow-up: {$lead->next_follow_up->format('Y-m-d')}";
            if ($lead->next_follow_up->isPast()) {
                $out .= ' (OVERDUE)';
            }
        }

        $out .= "\n[RECOMMENDATION]\nDraft a professional follow-up email referencing the lead's company and current stage.";

        return $out;
    }

    public function supplierSummary(Supplier $supplier): string
    {
        $totalPOs = $supplier->purchaseOrders()->count();
        $openPOs = $supplier->purchaseOrders()->whereIn('status', ['draft', 'pending', 'approved', 'ordered'])->count();
        $totalSpend = (float) $supplier->purchaseOrders()->where('status', 'received')->sum('total');
        $offerCount = $supplier->offers()->count();

        $out = "[FACT]\nSupplier: {$supplier->name}\n"
            ."Contact: {$supplier->email} | {$supplier->phone}\n"
            ."Total purchase orders: {$totalPOs} (open: {$openPOs})\n"
            ."Total spend (received): {$totalSpend}\n"
            ."Supplier offers: {$offerCount}";

        if ($openPOs > 0) {
            $out .= "\n[RECOMMENDATION]\n{$openPOs} open purchase orders. Review delivery timelines and payment status.";
        }

        return $out;
    }

    public function invoiceSummary(Invoice $invoice): string
    {
        $outstanding = (float) $invoice->outstandingBalance();
        $isPaid = $invoice->status === 'paid';
        $isOverdue = $invoice->status === 'overdue' || ($invoice->due_date && $invoice->due_date->isPast() && ! $isPaid);

        $out = "[FACT]\nInvoice: {$invoice->invoice_number}\n"
            ."Customer: {$invoice->customer?->name}\n"
            ."Status: {$invoice->status}\n"
            ."Total: {$invoice->total}\n"
            ."Paid: {$invoice->paid_amount}\n"
            ."Outstanding: {$outstanding}\n"
            ."Issue date: {$invoice->issue_date}\n"
            ."Due date: {$invoice->due_date}";

        if ($isOverdue) {
            $daysOverdue = now()->diffInDays($invoice->due_date);
            $out .= "\n[RECOMMENDATION]\nInvoice is {$daysOverdue} days overdue. Consider sending a payment reminder or escalation notice.";
        } elseif ($isPaid) {
            $out .= "\n[RECOMMENDATION]\nInvoice is fully paid. No action required.";
        } else {
            $out .= "\n[RECOMMENDATION]\nMonitor payment status. Follow up if due date approaches.";
        }

        return $out;
    }

    protected function render(string $label, int $count, ?float $total, array $details): string
    {
        $out = "[FACT]\n{$count} — {$label}.";
        if ($total !== null) {
            $out .= "\n[CALCULATION]\nTotal amount: {$total}.";
        }
        if ($details) {
            $out .= "\n".implode("\n", $details);
        }

        return $out;
    }
}
