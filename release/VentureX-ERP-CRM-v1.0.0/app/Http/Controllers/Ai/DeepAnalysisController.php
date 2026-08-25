<?php

namespace App\Http\Controllers\Ai;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Supplier;
use App\Services\Ai\AiPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Specialist high-value analyses for the AI Gateway.
 *
 * Every action is an explicit user trigger that only GENERATES analysis for
 * human review. Facts are aggregated locally first; only summarized context
 * reaches the AI; and if every AI provider fails, the local facts are returned
 * so the ERP never depends on a provider being up.
 *
 * These tasks route to Claude (see config/ai.php task_routing) because they are
 * complex, high-impact reasoning. The AI never approves a supplier, PO or
 * payment — a human reviews and confirms every recommendation.
 */
class DeepAnalysisController extends AiActionController
{
    public function supplier(Request $request): JsonResponse
    {
        $data = $request->validate(['supplier_id' => ['required', 'integer']]);
        $supplier = Supplier::ofCompany()->findOrFail($data['supplier_id']);
        $this->authorize('view', $supplier);

        $facts = $this->context->supplierDeep($supplier);

        return $this->respondWithLocalFallback($request, 'deep_supplier_analysis', 'supplier:deep:'.$supplier->id, $facts, function () use ($supplier, $facts) {
            return $this->gateway->chat(
                AiPrompt::system('senior procurement strategist')
                    .'Perform a DEEP supplier analysis for human review. Use ONLY the facts provided. Output sections: '
                    .'Summary; Strengths; Risks; Pricing and delivery observations; Negotiation opportunities; '
                    .'Recommended next action; Confidence level. Label each with [FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION]. '
                    .'Never recommend an action that requires AI approval — the recommendation is only for a human reviewer.',
                AiPrompt::task('deep_supplier_analysis', $facts, 'Deep-analyze this supplier for a sourcing decision.'),
                ['task' => 'deep_supplier_analysis', 'max_tokens' => 1800, 'context' => 'supplier:deep:'.$supplier->id]
            );
        });
    }

    public function negotiation(Request $request): JsonResponse
    {
        $data = $request->validate(['supplier_id' => ['required', 'integer']]);
        $supplier = Supplier::ofCompany()->findOrFail($data['supplier_id']);
        $this->authorize('view', $supplier);

        $facts = $this->context->negotiation($supplier);

        return $this->respondWithLocalFallback($request, 'negotiation_strategy', 'supplier:negotiation:'.$supplier->id, $facts, function () use ($supplier, $facts) {
            return $this->gateway->chat(
                AiPrompt::system('commercial negotiation advisor')
                    .'Draft a negotiation strategy for human use. Use ONLY the facts provided. Output sections: '
                    .'Target price strategy; Key negotiation points; Possible concessions; Questions to ask; '
                    .'A professional negotiation email draft. Label each with [FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION]. '
                    .'The email must never be sent automatically — it is a draft for a human.',
                AiPrompt::task('negotiation_strategy', $facts, 'Suggest a negotiation strategy for this supplier.'),
                ['task' => 'negotiation_strategy', 'max_tokens' => 1800, 'context' => 'supplier:negotiation:'.$supplier->id]
            );
        });
    }

    public function customer(Request $request): JsonResponse
    {
        $data = $request->validate(['customer_id' => ['required', 'integer']]);
        $customer = Customer::ofCompany()->findOrFail($data['customer_id']);
        $this->authorize('view', $customer);

        $facts = $this->context->customerDeep($customer);

        return $this->respondWithLocalFallback($request, 'deep_customer_analysis', 'customer:deep:'.$customer->id, $facts, function () use ($customer, $facts) {
            return $this->gateway->chat(
                AiPrompt::system('customer success strategist')
                    .'Perform a DEEP customer analysis for human review. Use ONLY the facts provided. Output sections: '
                    .'Customer health; Revenue opportunity; Retention risk; Follow-up priority; Upsell/cross-sell opportunities; '
                    .'Recommended next action. Label each with [FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION].',
                AiPrompt::task('deep_customer_analysis', $facts, 'Deep-analyze this customer.'),
                ['task' => 'deep_customer_analysis', 'max_tokens' => 1800, 'context' => 'customer:deep:'.$customer->id]
            );
        });
    }

    public function opportunity(Request $request): JsonResponse
    {
        $data = $request->validate(['opportunity_id' => ['required', 'integer']]);
        $opportunity = Opportunity::ofCompany()->findOrFail($data['opportunity_id']);
        $this->authorize('view', $opportunity);

        $facts = $this->context->opportunityDeep($opportunity);

        return $this->respondWithLocalFallback($request, 'opportunity_analysis', 'opportunity:deep:'.$opportunity->id, $facts, function () use ($opportunity, $facts) {
            return $this->gateway->chat(
                AiPrompt::system('sales deal strategist')
                    .'Analyze this sales opportunity for human review. Use ONLY the facts provided — never invent '
                    .'meetings, buyers or commitments. Output sections: Why this deal may close; Blockers; Missing '
                    .'information; Next steps; Strongest follow-up; Anticipated objection. Label each with '
                    .'[FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION].',
                AiPrompt::task('opportunity_analysis', $facts, 'Analyze this sales opportunity.'),
                ['task' => 'opportunity_analysis', 'max_tokens' => 1800, 'context' => 'opportunity:deep:'.$opportunity->id]
            );
        });
    }

    public function procurement(Request $request): JsonResponse
    {
        $facts = $this->context->procurementDeep();

        return $this->respondWithLocalFallback($request, 'supplier_comparison', 'procurement:deep', $facts, function () use ($facts) {
            return $this->gateway->chat(
                AiPrompt::system('procurement strategist')
                    .'Perform a DEEP procurement analysis for human review. Use ONLY the facts provided. Output sections: '
                    .'Best-value supplier(s); Reasons; Risks; Negotiation opportunities; Alternative suppliers; '
                    .'Recommended next action. Label each with [FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION]. '
                    .'Do NOT create a purchase order — the recommendation is reviewed and confirmed by a human first.',
                AiPrompt::task('supplier_comparison', $facts, 'Deep-analyze procurement options.'),
                ['task' => 'supplier_comparison', 'max_tokens' => 2200, 'context' => 'procurement:deep']
            );
        });
    }
}
