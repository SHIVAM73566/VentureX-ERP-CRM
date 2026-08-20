<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\Ai\AiContextBuilder;
use App\Services\Ai\AiException;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AiLocalIntelligence;
use App\Services\Ai\AiPrompt;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * One-click AI actions. Every action is an explicit user trigger that only
 * GENERATES text (summaries, drafts, analysis) for human review. No AI action
 * mutates ERP data directly.
 */
class AiActionController extends Controller
{
    public function __construct(
        protected AiGateway $gateway,
        protected AiContextBuilder $context,
        protected AiLocalIntelligence $local,
    ) {}

    public function customerSummary(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $data = $request->validate(['customer_id' => ['required', 'integer']]);
        $customer = Customer::ofCompany()->findOrFail($data['customer_id']);
        $this->authorize('view', $customer);

        $facts = $this->local->customerSummary($customer);

        return $this->respondWithLocalFallback($request, 'customer_summary', 'customer:'.$customer->id, $facts, function () use ($customer) {
            return $this->gateway->chat(
                AiPrompt::system('customer relationship analyst'),
                AiPrompt::task('customer_summary', $this->context->customer($customer), 'Summarize this customer: overview, relationship, open opportunities, outstanding invoices, risks, and the recommended next action.'),
                ['task' => 'customer_summary', 'max_tokens' => 900, 'context' => 'customer:'.$customer->id]
            );
        });
    }

    public function leadEmail(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $data = $request->validate(['lead_id' => ['required', 'integer']]);
        $lead = Lead::ofCompany()->findOrFail($data['lead_id']);
        $this->authorize('view', $lead);

        $facts = $this->local->leadSummary($lead);

        return $this->respondWithLocalFallback($request, 'lead_email', 'lead:'.$lead->id, $facts, function () use ($lead) {
            return $this->gateway->chat(
                AiPrompt::system('sales assistant')
                    .'Write a short, professional follow-up email (max 180 words). Use only the facts provided. Do not invent dates, amounts or commitments.',
                AiPrompt::task('lead_email', $this->context->lead($lead), 'Draft a polite follow-up email for this lead.'),
                ['task' => 'lead_email', 'max_tokens' => 500, 'context' => 'lead:'.$lead->id]
            );
        });
    }

    public function supplierAnalysis(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $data = $request->validate(['supplier_id' => ['required', 'integer']]);
        $supplier = Supplier::ofCompany()->findOrFail($data['supplier_id']);
        $this->authorize('view', $supplier);

        $facts = $this->local->supplierSummary($supplier);

        return $this->respondWithLocalFallback($request, 'supplier_analysis', 'supplier:'.$supplier->id, $facts, function () use ($supplier) {
            return $this->gateway->chat(
                AiPrompt::system('procurement analyst')
                    .'Output: summary, strengths, risks, cost opportunity, and a recommended action. Label each with [FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION].',
                AiPrompt::task('supplier_analysis', $this->context->supplier($supplier), 'Analyze this supplier.'),
                ['task' => 'supplier_analysis', 'max_tokens' => 1000, 'context' => 'supplier:'.$supplier->id]
            );
        });
    }

    public function invoiceSummary(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $data = $request->validate(['invoice_id' => ['required', 'integer']]);
        $invoice = Invoice::ofCompany()->findOrFail($data['invoice_id']);
        $this->authorize('view', $invoice);

        $facts = $this->local->invoiceSummary($invoice);

        return $this->respondWithLocalFallback($request, 'finance_analysis', 'invoice:'.$invoice->id, $facts, function () use ($invoice) {
            return $this->gateway->chat(
                AiPrompt::system('finance analyst')
                    .'Never invent financial figures. All amounts must come from the context. Explain payment risk and recommend a next step.',
                AiPrompt::task('finance_analysis', $this->context->invoice($invoice), 'Explain the payment situation and risk for this invoice, and recommend a next step.'),
                ['task' => 'finance_analysis', 'max_tokens' => 800, 'context' => 'invoice:'.$invoice->id]
            );
        });
    }

    public function inventory(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $data = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'question' => ['required', 'string', 'max:2000'],
        ]);

        $local = $this->local->answer($data['question']);
        if ($local !== null) {
            return response()->json(['content' => $local, 'mode' => 'local', 'cached' => false]);
        }

        $context = 'inventory';
        if (! empty($data['product_id'])) {
            $product = Product::ofCompany()->findOrFail($data['product_id']);
            $this->authorize('view', $product);
            $context = 'product:'.$product->id;
        }

        return $this->respond($request, 'inventory_analysis', $context, function () use ($data, $context) {
            return $this->gateway->chat(
                AiPrompt::system('inventory analyst')
                    .'Stock calculations are done locally; provide higher-level interpretation only. Do not invent stock figures.',
                AiPrompt::task('inventory_analysis', $this->context->inventory(), $data['question']),
                ['task' => 'inventory_analysis', 'max_tokens' => 800, 'context' => $context]
            );
        });
    }

    public function finance(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $data = $request->validate(['question' => ['required', 'string', 'max:2000']]);

        $local = $this->local->answer($data['question']);
        if ($local !== null) {
            return response()->json(['content' => $local, 'mode' => 'local', 'cached' => false]);
        }

        return $this->respond($request, 'finance_analysis', 'finance', function () use ($data) {
            return $this->gateway->chat(
                AiPrompt::system('finance analyst')
                    .'All financial facts must come from the context. Never fabricate amounts.',
                AiPrompt::task('finance_analysis', $this->context->receivablesPayables(), $data['question']),
                ['task' => 'finance_analysis', 'max_tokens' => 900, 'context' => 'finance']
            );
        });
    }

    protected function respond(Request $request, string $task, string $context, callable $call): JsonResponse
    {
        $this->authorize('viewAny', AiRun::class);

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => auth()->id(),
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'status' => 'running',
            'input_type' => $task,
            'input' => ['context' => $context, 'question' => $request->input('question')],
            'started_at' => now(),
        ]);

        try {
            $result = $call();

            $run->update([
                'status' => 'completed',
                'provider' => $result['provider'],
                'model' => $result['model'],
                'output' => ['content' => $result['content']],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'cost' => $result['cost'],
                'finished_at' => now(),
            ]);

            AuditLogger::log('ai_action', 'ai', null, null, [
                'run_id' => $run->id,
                'task' => $task,
                'cached' => $result['cached'],
                'provider' => $result['provider'],
            ]);

            return response()->json([
                'content' => $result['content'],
                'mode' => 'ai',
                'cached' => $result['cached'],
                'provider' => $result['provider'],
                'model' => $result['model'],
                'run_id' => $run->id,
            ]);
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);
            Log::warning('AI action failed', ['run' => $run->id, 'task' => $task, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'AI processing failed. Please try again later.'], 502);
        }
    }

    /**
     * Like respond(), but if every AI provider fails it returns the locally
     * computed business facts so the user still gets value and the ERP never
     * depends on an AI provider being up.
     */
    protected function respondWithLocalFallback(Request $request, string $task, string $context, string $facts, callable $call): JsonResponse
    {
        ini_set('max_execution_time', '180');
        $this->authorize('viewAny', AiRun::class);

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => auth()->id(),
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'status' => 'running',
            'input_type' => $task,
            'input' => ['context' => $context, 'question' => $request->input('question')],
            'started_at' => now(),
        ]);

        try {
            $result = $call();

            $run->update([
                'status' => 'completed',
                'provider' => $result['provider'],
                'model' => $result['model'],
                'output' => ['content' => $result['content']],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'cost' => $result['cost'],
                'finished_at' => now(),
            ]);

            AuditLogger::log('ai_action', 'ai', null, null, [
                'run_id' => $run->id,
                'task' => $task,
                'cached' => $result['cached'],
                'provider' => $result['provider'],
            ]);

            return response()->json([
                'content' => $result['content'],
                'mode' => 'ai',
                'cached' => $result['cached'],
                'provider' => $result['provider'],
                'model' => $result['model'],
                'run_id' => $run->id,
            ]);
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);
            Log::warning('AI action failed, serving local facts', ['run' => $run->id, 'task' => $task]);

            return response()->json([
                'content' => "AI analysis is currently unavailable. Here are the relevant business facts:\n\n{$facts}",
                'mode' => 'local',
                'cached' => false,
                'provider' => null,
                'model' => null,
                'run_id' => $run->id,
            ]);
        }
    }
}
