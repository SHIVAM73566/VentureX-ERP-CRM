<?php

namespace App\Services\Ai;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\SupplierOffer;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Cache;

/**
 * Composes the AI Business Insights widget.
 *
 * Insights come from three sources in priority order:
 *   1. Database + business rules (always cheap, always shown)
 *   2. Cached AI-generated insights (24h TTL, produced on explicit request)
 *   3. Never a fresh AI call on page load.
 */
class AiInsightService
{
    public function __construct(protected AiGateway $gateway) {}

    /**
     * Whether an AI provider is configured (drives the generate button).
     */
    public function gatewayEnabled(): bool
    {
        return $this->gateway->isEnabled();
    }

    /**
     * Rule-based insights computed directly from the database.
     */
    public function rules(?int $companyId = null): array
    {
        $insights = [];

        $leadsDue = Lead::ofCompany($companyId)
            ->whereNotNull('next_follow_up')
            ->where('status', '!=', 'won')
            ->where('next_follow_up', '<=', now()->addDay())
            ->count();

        if ($leadsDue > 0) {
            $insights[] = $this->make($leadsDue > 3 ? 'critical' : 'high', $leadsDue.' lead'.($leadsDue === 1 ? '' : 's').' need'.($leadsDue === 1 ? 's' : '').' follow-up', route('leads.index'));
        }

        $overdue = Invoice::ofCompany($companyId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->count();

        if ($overdue > 0) {
            $insights[] = $this->make('critical', $overdue.' invoice'.($overdue === 1 ? ' is' : 's are').' overdue', route('sales.invoices.index'));
        }

        $lowStock = Product::ofCompany($companyId)
            ->where('reorder_level', '>', 0)
            ->get()
            ->filter(fn ($p) => $p->isLowStock())
            ->count();

        if ($lowStock > 0) {
            $insights[] = $this->make($lowStock > 4 ? 'high' : 'normal', $lowStock.' product'.($lowStock === 1 ? '' : 's').' below reorder level', route('inventory.products.index'));
        }

        $pendingPOs = PurchaseOrder::ofCompany($companyId)
            ->whereIn('status', ['draft', 'pending_approval'])
            ->count();

        if ($pendingPOs > 0) {
            $insights[] = $this->make('high', $pendingPOs.' purchase order'.($pendingPOs === 1 ? '' : 's').' require'.($pendingPOs === 1 ? 's' : '').' attention', route('procurement.orders.index'));
        }

        $openRequisitions = PurchaseRequisition::ofCompany($companyId)
            ->whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->count();

        if ($openRequisitions > 0) {
            $insights[] = $this->make('normal', $openRequisitions.' purchase requisition'.($openRequisitions === 1 ? ' is' : 's are').' open', route('procurement.requisitions.index'));
        }

        $outstanding = Invoice::ofCompany($companyId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->get()
            ->sum(fn ($i) => (float) $i->outstandingBalance());

        if ($outstanding > 0) {
            $insights[] = $this->make('high', 'Receivables outstanding: '.number_format($outstanding), route('finance.receivables'));
        }

        $redOffers = SupplierOffer::ofCompany($companyId)
            ->where('quality_status', 'RED')
            ->count();

        if ($redOffers > 0) {
            $insights[] = $this->make('critical', $redOffers.' supplier offer'.($redOffers === 1 ? '' : 's').' flagged red', route('supplier-offers.index'));
        }

        $openOpportunities = Opportunity::ofCompany($companyId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->count();

        $wonOpportunities = Opportunity::ofCompany($companyId)
            ->where('stage', 'won')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        if ($wonOpportunities > 0) {
            $insights[] = $this->make('normal', $wonOpportunities.' opportunity'.($wonOpportunities === 1 ? '' : 'ies').' won in the last 30 days', route('opportunities.index'));
        } elseif ($openOpportunities > 0) {
            $insights[] = $this->make('normal', $openOpportunities.' open opportunities in your pipeline', route('pipeline.index'));
        }

        return $insights;
    }

    /**
     * Cached AI-generated insights, or null when none have been generated yet.
     */
    public function cachedAi(?int $companyId = null): ?array
    {
        $cached = Cache::get($this->aiKey($companyId));

        return is_array($cached) ? $cached : null;
    }

    /**
     * Generate AI insights (explicit user action only) and cache for 24h.
     *
     * @throws AiException when AI is unavailable.
     */
    public function generate(?int $companyId = null): array
    {
        $companyId = $companyId ?? CompanyContext::id();
        $rules = $this->rules($companyId);

        $context = 'Company: '.(CompanyContext::current()?->name ?? 'n/a')."\n"
            ."Current business signals (computed from the database):\n"
            .(collect($rules)->isNotEmpty()
                ? collect($rules)->map(fn ($r) => '- '.$r['text'])->implode("\n")
                : 'No critical signals detected.')
            ."\n\nYou may only reference these numbers. Do not invent figures.";

        $system = AiPrompt::system('insights analyst')
            .'Your job: turn the business signals into 3-5 prioritized, actionable insights. '
            .'Label each line [FACT], [CALCULATION] or [RECOMMENDATION]. Keep each insight to one sentence.';

        $result = $this->gateway->chat($system, $context, [
            'task' => 'insights',
            'context' => 'company-insights',
            'max_tokens' => 800,
        ]);

        $insights = [
            'content' => $result['content'],
            'generated_at' => now()->toIso8601String(),
            'provider' => $result['provider'],
            'model' => $result['model'],
            'cached' => $result['cached'],
        ];

        Cache::put($this->aiKey($companyId), $insights, (int) config('ai.cache_ttl', 86400));

        return $insights;
    }

    /**
     * Invalidate cached AI insights after relevant business mutations.
     */
    public function forget(?int $companyId = null): void
    {
        Cache::forget($this->aiKey($companyId ?? CompanyContext::id()));
    }

    protected function aiKey(?int $companyId): string
    {
        return 'ai:'.($companyId ?? CompanyContext::id()).':company-insights';
    }

    protected function make(string $tone, string $text, ?string $url = null): array
    {
        return ['tone' => $tone, 'text' => $text, 'url' => $url];
    }
}
