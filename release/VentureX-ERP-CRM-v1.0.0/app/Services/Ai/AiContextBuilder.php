<?php

namespace App\Services\Ai;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Services\CompanyContext;

/**
 * Builds compact, relevant business context for AI prompts. Only necessary
 * fields are included to reduce tokens, cost, latency and privacy exposure.
 */
class AiContextBuilder
{
    public function customer(Customer $customer): string
    {
        $industry = $customer->industry ?? 'n/a';
        $city = $customer->city ?? 'n/a';
        $contact = $customer->primary_contact_name ?? 'n/a';

        $lines = [
            "Customer: {$customer->name} (id {$customer->id})",
            "Status: {$customer->status} | Industry: {$industry} | City: {$city}",
            "Contact: {$contact}",
        ];

        $open = $customer->opportunities()
            ->whereNotIn('stage', ['won', 'lost'])
            ->orderByDesc('expected_value')
            ->limit(5)
            ->get();

        if ($open->count()) {
            $lines[] = 'Open opportunities: '.$open->map(fn ($o) => "{$o->title} ({$o->stage}, value {$o->expected_value})")->implode('; ');
        }

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        if ($invoices->count()) {
            $lines[] = 'Open invoices: '.$invoices->map(fn ($i) => "{$i->invoice_number} ({$i->status}, due {$i->due_date}, outstanding {$i->outstandingBalance()})")->implode('; ');
        }

        return implode("\n", $lines);
    }

    public function lead(Lead $lead): string
    {
        $source = $lead->source ?? 'n/a';
        $next = $lead->next_follow_up ? $lead->next_follow_up->format('Y-m-d') : 'not scheduled';
        $notes = substr((string) $lead->notes, 0, 300);

        return implode("\n", [
            "Lead: [REDACTED_NAME] (id {$lead->id})",
            "Company: {$lead->company_name} | Status: {$lead->status}",
            "Source: {$source} | Next follow-up: {$next}",
            "Notes: {$notes}",
        ]);
    }

    public function supplier(Supplier $supplier): string
    {
        $code = $supplier->supplier_code ?? 'n/a';
        $country = $supplier->country?->name ?? 'n/a';
        $currency = $supplier->currency_code ?? 'USD';
        $terms = $supplier->payment_terms ?? 'n/a';

        $lines = [
            "Supplier: {$supplier->name} (id {$supplier->id})",
            "Code: {$code} | Status: {$supplier->status} | Country: {$country}",
            "Currency: {$currency} | Terms: {$terms}",
        ];

        $offers = SupplierOffer::where('supplier_id', $supplier->id)
            ->latest()
            ->limit(8)
            ->get();

        if ($offers->count()) {
            $lines[] = 'Recent offers: '.$offers->map(fn ($o) => "{$o->material_description} {$o->grade} | qty {$o->quantity_mt} MT | {$o->price_per_mt} {$o->currency_code}/MT | status {$o->quality_status} | risk {$o->risk_level}")->implode('; ');
        }

        $orders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['open', 'sent', 'approved'])
            ->count();
        $lines[] = "Open purchase orders: {$orders}";

        return implode("\n", $lines);
    }

    public function invoice(Invoice $invoice): string
    {
        $customer = $invoice->customer?->name ?? 'n/a';
        $issue = $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : 'n/a';
        $due = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'n/a';

        return implode("\n", [
            "Invoice: {$invoice->invoice_number} (id {$invoice->id})",
            "Customer: {$customer}",
            "Status: {$invoice->status} | Issue: {$issue} | Due: {$due}",
            "Total: {$invoice->total} | Paid: {$invoice->paid_amount} | Outstanding: {$invoice->outstandingBalance()}",
        ]);
    }

    public function product(Product $product): string
    {
        $category = $product->category ?? 'n/a';

        return implode("\n", [
            "Product: {$product->name} (sku {$product->sku}, id {$product->id})",
            "Category: {$category}",
            "Purchase price: {$product->purchase_price} | Selling price: {$product->selling_price}",
            "Available stock: {$product->availableStock()} | Reorder level: {$product->reorder_level} | Below reorder: ".($product->isLowStock() ? 'yes' : 'no'),
        ]);
    }

    public function inventory(): string
    {
        $low = Product::ofCompany()
            ->where('reorder_level', '>', 0)
            ->get()
            ->filter(fn ($p) => $p->isLowStock())
            ->values();

        $lines = ["Low stock products: {$low->count()}"];
        if ($low->count()) {
            $lines[] = $low->take(10)->map(fn ($p) => "{$p->name} (available {$p->availableStock()} / reorder {$p->reorder_level})")->implode('; ');
        }

        return implode("\n", $lines);
    }

    public function receivablesPayables(): string
    {
        $receivables = Invoice::ofCompany()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->get();

        $outstanding = $receivables->sum(fn ($i) => (float) $i->outstandingBalance());
        $overdue = $receivables->filter(fn ($i) => $i->due_date && $i->due_date->isPast())->count();

        return implode("\n", [
            "Open receivable invoices: {$receivables->count()}",
            "Outstanding receivables total: {$outstanding}",
            "Overdue: {$overdue}",
        ]);
    }

    public function procurement(): string
    {
        $open = PurchaseRequisition::ofCompany()
            ->with('items')
            ->whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->latest()
            ->limit(10)
            ->get();

        $offers = SupplierOffer::ofCompany()
            ->with('supplier')
            ->latest()
            ->limit(15)
            ->get();

        $requisitions = $open->count()
            ? $open->map(fn ($r) => "{$r->pr_number} ({$r->priority}, {$r->status}, items {$r->items->count()})")->implode('; ')
            : 'none';

        $recentOffers = $offers->count()
            ? $offers->map(fn ($o) => "{$o->supplier?->name} | {$o->material_description} {$o->grade} | {$o->quantity_mt} MT | {$o->price_per_mt} {$o->currency_code}/MT | {$o->quality_status}")->implode('; ')
            : 'none';

        return implode("\n---\n", [
            "Open requisitions: {$requisitions}",
            "Recent supplier offers: {$recentOffers}",
        ]);
    }

    public function supplierDeep(Supplier $supplier): string
    {
        $offers = $supplier->offers()->latest()->limit(12)->get();

        $quality = $offers->countBy(fn ($o) => (string) $o->quality_status);
        $risk = $offers->countBy(fn ($o) => (string) ($o->risk_level ?? 'n/a'));
        $avgPrice = $offers->isNotEmpty()
            ? round($offers->avg(fn ($o) => (float) $o->price_per_mt), 2)
            : 0;

        $openOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->get();

        $openValue = $openOrders->sum(fn ($o) => (float) $o->outstandingBalance());

        $supplierPoIds = PurchaseOrder::where('supplier_id', $supplier->id)->pluck('id');
        $delayed = Shipment::whereIn('purchase_order_id', $supplierPoIds)->where('status', 'delayed')->count();
        $delivered = Shipment::whereIn('purchase_order_id', $supplierPoIds)->where('status', 'delivered')->count();

        $country = $supplier->country?->name ?? 'n/a';
        $currency = $supplier->currency_code ?? 'n/a';
        $paymentTerms = $supplier->payment_terms ?? 'n/a';
        $qualityGreen = $quality['GREEN'] ?? 0;
        $qualityYellow = $quality['YELLOW'] ?? 0;
        $qualityRed = $quality['RED'] ?? 0;

        $lines = [
            "Supplier: {$supplier->name} (id {$supplier->id})",
            "Code: {$supplier->supplier_code} | Status: {$supplier->status} | Country: {$country}",
            "Currency: {$currency} | Payment terms: {$paymentTerms}",
            "Active offers: {$offers->count()} | Avg price/MT: {$avgPrice}",
            "Quality mix: green {$qualityGreen}, yellow {$qualityYellow}, red {$qualityRed}",
            'Risk levels: '.($risk->isNotEmpty() ? $risk->map(fn ($c, $r) => "{$r} {$c}")->implode(', ') : 'none'),
            "Open purchase orders: {$openOrders->count()} | Outstanding PO value: {$openValue}",
            "Inbound shipments: {$delivered} delivered, {$delayed} delayed",
        ];

        if ($supplier->notes) {
            $lines[] = 'Notes: '.substr((string) $supplier->notes, 0, 300);
        }

        return implode("\n", $lines);
    }

    /**
     * Negotiation context — pricing history, terms and volumes by material.
     */
    public function negotiation(Supplier $supplier): string
    {
        $offers = $supplier->offers()->latest()->limit(15)->get();

        $byMaterial = $offers->groupBy(fn ($o) => ($o->material_category ?? 'other').' '.($o->grade ?? ''));

        $paymentTerms = $supplier->payment_terms ?? 'n/a';
        $currencyCode = $supplier->currency_code ?? 'n/a';

        $lines = [
            "Supplier: {$supplier->name} (id {$supplier->id})",
            "Payment terms: {$paymentTerms} | Currency: {$currencyCode}",
        ];

        foreach ($byMaterial as $material => $group) {
            $prices = $group->map(fn ($o) => (float) $o->price_per_mt);
            $latest = $group->first();
            $lines[] = sprintf(
                '%s | offers %d | latest %s %s/MT | range %s-%s %s/MT | qty %.2f MT | quality %s',
                $material,
                $group->count(),
                $latest->price_per_mt,
                $latest->currency_code ?? 'USD',
                $prices->min(),
                $prices->max(),
                $latest->currency_code ?? 'USD',
                (float) $latest->quantity_mt,
                $latest->quality_status,
            );
        }

        $orders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['approved', 'ordered', 'partially_received', 'received'])
            ->get();

        $lines[] = "Historical orders: {$orders->count()} | Total value: "
            .$orders->sum(fn ($o) => (float) $o->total);

        return implode("\n", $lines);
    }

    /**
     * Deep customer context — relationship, spend, outstanding and pipeline.
     */
    public function customerDeep(Customer $customer): string
    {
        $invoices = Invoice::where('customer_id', $customer->id)->get();
        $outstandingInvoices = $invoices->whereIn('status', ['unpaid', 'partial', 'overdue']);

        $totalInvoiced = $invoices->sum(fn ($i) => (float) $i->total);
        $outstanding = $outstandingInvoices->sum(fn ($i) => (float) $i->outstandingBalance());
        $overdue = $outstandingInvoices->filter(fn ($i) => $i->due_date && $i->due_date->isPast())->count();
        $lastYear = $invoices->filter(fn ($i) => $i->issue_date && $i->issue_date->isAfter(now()->subYear()))
            ->sum(fn ($i) => (float) $i->total);

        $open = $customer->opportunities()->whereNotIn('stage', ['won', 'lost'])->get();
        $openValue = $open->sum(fn ($o) => (float) $o->expected_value);
        $wonValue = $customer->opportunities()->where('stage', 'won')
            ->where('updated_at', '>=', now()->subDays(90))
            ->sum('expected_value');

        $industry = $customer->industry ?? 'n/a';
        $city = $customer->city ?? 'n/a';

        $lines = [
            "Customer: {$customer->name} (id {$customer->id})",
            "Status: {$customer->status} | Industry: {$industry} | City: {$city}",
            "Contacts: {$customer->contacts()->count()}",
            "Lifetime invoiced: {$totalInvoiced} | Last 12 months invoiced: {$lastYear}",
            "Open invoice value: {$outstanding} | Overdue invoices: {$overdue}",
            "Open opportunities: {$open->count()} (value {$openValue}) | Won value (90d): {$wonValue}",
        ];

        $recent = $customer->activities()->latest()->limit(5)->get();
        if ($recent->isNotEmpty()) {
            $lines[] = 'Recent activity: '.$recent->map(fn ($a) => "{$a->type} | {$a->subject} | {$a->activity_date?->format('Y-m-d')}")->implode('; ');
        }

        return implode("\n", $lines);
    }

    /**
     * Sales opportunity context for win/loss analysis.
     */
    public function opportunityDeep(Opportunity $opportunity): string
    {
        $customer = $opportunity->customer;
        $customerIndustry = $customer?->industry ?? 'n/a';
        $customerLine = $customer
            ? "Customer: {$customer->name} (status {$customer->status}, industry {$customerIndustry})"
            : 'Customer: unlinked';

        $customerOutstanding = 0;
        $customerOverdue = 0;
        if ($customer) {
            $invoices = Invoice::where('customer_id', $customer->id)->whereIn('status', ['unpaid', 'partial', 'overdue'])->get();
            $customerOutstanding = $invoices->sum(fn ($i) => (float) $i->outstandingBalance());
            $customerOverdue = $invoices->filter(fn ($i) => $i->due_date && $i->due_date->isPast())->count();
        }

        $otherOpen = $customer
            ? $customer->opportunities()->where('id', '!=', $opportunity->id)->whereNotIn('stage', ['won', 'lost'])->get()
            : collect();

        $assignedTo = $opportunity->assignedTo?->name ?? 'unassigned';
        $source = $opportunity->source ?? 'n/a';

        $lines = [
            "Opportunity: {$opportunity->name} (id {$opportunity->id})",
            "Stage: {$opportunity->stage} | Value: {$opportunity->expected_value} | Probability: {$opportunity->probability}%",
            'Close date: '.($opportunity->expected_close_date ? $opportunity->expected_close_date->format('Y-m-d') : 'not set'),
            "Assigned to: {$assignedTo} | Source: {$source}",
            $customerLine,
            "Customer outstanding: {$customerOutstanding} | Customer overdue: {$customerOverdue}",
            'Other open opportunities for this customer: '.($otherOpen->isNotEmpty()
                ? $otherOpen->map(fn ($o) => "{$o->name} ({$o->stage}, {$o->expected_value})")->implode('; ')
                : 'none'),
        ];

        if ($opportunity->notes) {
            $lines[] = 'Notes: '.substr((string) $opportunity->notes, 0, 300);
        }

        $recent = $opportunity->activities()->latest()->limit(5)->get();
        if ($recent->isNotEmpty()) {
            $lines[] = 'Recent activity: '.$recent->map(fn ($a) => "{$a->type} | {$a->subject} | {$a->activity_date?->format('Y-m-d')}")->implode('; ');
        }

        return implode("\n", $lines);
    }

    /**
     * Cross-supplier comparison context for deep procurement analysis.
     */
    public function procurementDeep(): string
    {
        $offers = SupplierOffer::ofCompany()
            ->with('supplier')
            ->latest()
            ->limit(25)
            ->get();

        $offerLines = $offers->map(fn ($o) => sprintf(
            '%s | supplier=%s | cat=%s | grade=%s | qty_mt=%.2f | price_mt=%.2f %s | quality=%s | risk=%s',
            $o->id,
            $o->supplier?->name ?? 'unlinked',
            $o->material_category,
            $o->grade ?? 'n/a',
            (float) $o->quantity_mt,
            (float) $o->price_per_mt,
            $o->currency_code ?? 'USD',
            $o->quality_status,
            $o->risk_level ?? 'n/a',
        ));

        $requisitions = PurchaseRequisition::ofCompany()
            ->with('items')
            ->whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->latest()
            ->limit(10)
            ->get();

        $reqLines = $requisitions->map(fn ($r) => sprintf(
            '%s | priority=%s | status=%s | items=%d',
            $r->pr_number ?? ('#'.$r->id),
            $r->priority,
            $r->status,
            $r->items->count(),
        ));

        return implode("\n---\n", [
            "Supplier offers (latest {$offers->count()}):\n".($offerLines->isNotEmpty() ? $offerLines->implode("\n") : 'none'),
            "Open requisitions (latest {$requisitions->count()}):\n".($reqLines->isNotEmpty() ? $reqLines->implode("\n") : 'none'),
        ]);
    }

    /**
     * Executive review facts — the business KPIs computed locally. Only these
     * summarized facts are sent to the AI; no full tables ever leave the ERP.
     */
    public function executive(): string
    {
        $company = CompanyContext::current();
        $companyName = $company?->name ?? 'n/a';
        $lines = ["Company: {$companyName}\n"];

        $receivables = Invoice::ofCompany()->whereIn('status', ['unpaid', 'partial', 'overdue'])->get();
        $lines[] = sprintf(
            'Receivables: %d open invoices, %.2f outstanding, %d overdue.',
            $receivables->count(),
            $receivables->sum(fn ($i) => (float) $i->outstandingBalance()),
            $receivables->filter(fn ($i) => $i->due_date && $i->due_date->isPast())->count(),
        );

        $paid30 = Invoice::ofCompany()->whereIn('status', ['paid', 'partial'])
            ->where('updated_at', '>=', now()->subDays(30))
            ->sum('paid_amount');
        $lines[] = "Payments received in the last 30 days: {$paid30}.";

        $payables = PurchaseOrder::ofCompany()->whereIn('payment_status', ['unpaid', 'partial'])->get();
        $lines[] = sprintf(
            'Payables: %d purchase orders with outstanding balance totaling %.2f.',
            $payables->count(),
            $payables->sum(fn ($o) => (float) $o->outstandingBalance()),
        );

        $open = Opportunity::ofCompany()->whereNotIn('stage', ['won', 'lost'])->get();
        $won30 = Opportunity::ofCompany()->where('stage', 'won')->where('updated_at', '>=', now()->subDays(30))->get();
        $lines[] = sprintf(
            'Sales pipeline: %d open opportunities worth %.2f; %d won in the last 30 days.',
            $open->count(),
            $open->sum(fn ($o) => (float) $o->expected_value),
            $won30->count(),
        );

        $lowStock = Product::ofCompany()->where('reorder_level', '>', 0)->get()->filter(fn ($p) => $p->isLowStock());
        $lines[] = "Inventory: {$lowStock->count()} products below reorder level.";

        $pendingPOs = PurchaseOrder::ofCompany()->whereIn('status', ['draft', 'pending'])->get();
        $redOffers = SupplierOffer::ofCompany()->where('quality_status', 'RED')->get();
        $lines[] = sprintf(
            'Procurement: %d purchase orders pending approval, %d supplier offers flagged red.',
            $pendingPOs->count(),
            $redOffers->count(),
        );

        $overdueLeads = Lead::ofCompany()
            ->whereNotNull('next_follow_up')
            ->where('status', '!=', 'won')
            ->where('next_follow_up', '<=', now()->addDay())
            ->count();
        $lines[] = "CRM: {$overdueLeads} leads require follow-up, {$open->count()} open opportunities.";

        $delayedShipments = Shipment::ofCompany()->where('status', 'delayed')->get();
        $lines[] = "Logistics: {$delayedShipments->count()} shipments currently delayed.";

        return implode("\n", $lines);
    }

    /**
     * "What should I do today?" facts — actionable signals computed locally.
     */
    public function daily(): string
    {
        $company = CompanyContext::current();
        $companyName = $company?->name ?? 'n/a';
        $lines = ["Company: {$companyName}\n"];

        $overdue = Invoice::ofCompany()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->get();
        $lines[] = $overdue->isNotEmpty()
            ? 'Overdue invoices: '.$overdue->take(8)->map(fn ($i) => "{$i->invoice_number} (due {$i->due_date}, outstanding {$i->outstandingBalance()})")->implode('; ')
            : 'Overdue invoices: none.';

        $leadsDue = Lead::ofCompany()
            ->whereNotNull('next_follow_up')
            ->where('status', '!=', 'won')
            ->where('next_follow_up', '<=', now()->addDay())
            ->get();
        $lines[] = $leadsDue->isNotEmpty()
            ? 'Leads needing follow-up: '.$leadsDue->take(6)->map(fn ($l) => "{$l->company_name} (next {$l->next_follow_up})")->implode('; ')
            : 'Leads needing follow-up: none.';

        $stalled = Opportunity::ofCompany()
            ->whereNotIn('stage', ['won', 'lost'])
            ->where('updated_at', '<', now()->subDays(14))
            ->get();
        $lines[] = $stalled->isNotEmpty()
            ? 'Stalled opportunities (no update in 14+ days): '.$stalled->take(5)->map(fn ($o) => "{$o->name} ({$o->stage}, {$o->expected_value})")->implode('; ')
            : 'Stalled opportunities: none.';

        $lowStock = Product::ofCompany()->where('reorder_level', '>', 0)->get()->filter(fn ($p) => $p->isLowStock());
        $lines[] = $lowStock->isNotEmpty()
            ? 'Low stock: '.$lowStock->take(6)->map(fn ($p) => "{$p->name} (available {$p->availableStock()} / reorder {$p->reorder_level})")->implode('; ')
            : 'Low stock: none.';

        $delayedShipments = Shipment::ofCompany()->where('status', 'delayed')->get();
        $lines[] = $delayedShipments->isNotEmpty()
            ? 'Delayed shipments: '.$delayedShipments->take(5)->map(fn ($s) => "{$s->shipment_number} ({$s->carrier})")->implode('; ')
            : 'Delayed shipments: none.';

        $pendingPOs = PurchaseOrder::ofCompany()->whereIn('status', ['draft', 'pending'])->get();
        $lines[] = $pendingPOs->isNotEmpty()
            ? 'Purchase orders awaiting approval: '.$pendingPOs->take(5)->map(fn ($o) => "{$o->po_number} ({$o->total})")->implode('; ')
            : 'Purchase orders awaiting approval: none.';

        $redOffers = SupplierOffer::ofCompany()->where('quality_status', 'RED')->get();
        $lines[] = $redOffers->isNotEmpty()
            ? 'Supplier offers flagged red: '.$redOffers->take(5)->map(fn ($o) => "{$o->material_description} from {$o->supplier?->name}")->implode('; ')
            : 'Supplier offers flagged red: none.';

        return implode("\n", $lines);
    }
}
