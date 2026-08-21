<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
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
use Illuminate\View\View;

/**
 * Business Copilot — simple, natural-language business intelligence.
 *
 * The backend decides whether to answer from local data, cache, or one AI
 * provider. Users never see models, providers, temperatures or tokens.
 */
class CopilotController extends Controller
{
    public function __construct(
        protected AiGateway $gateway,
        protected AiLocalIntelligence $local,
        protected AiContextBuilder $context,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AiRun::class);

        return view('ai.copilot');
    }

    public function ask(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');
        $this->authorize('create', AiRun::class);

        $data = $request->validate(['question' => ['required', 'string', 'max:2000']]);
        $question = $data['question'];

        // 1. Local intelligence first (free, instant).
        $local = $this->local->answer($question);
        if ($local !== null) {
            return response()->json(['content' => $local, 'mode' => 'local', 'cached' => false]);
        }

        // 2. Otherwise route to ONE provider with the right context.
        [$task, $context] = $this->resolveDomain($question);

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => auth()->id(),
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'status' => 'running',
            'input_type' => 'copilot',
            'input' => ['question' => $question, 'task' => $task],
            'started_at' => now(),
        ]);

        try {
            $result = $this->gateway->chat(
                AiPrompt::system('business copilot')
                    .'Answer the user question in plain language. Use only the provided context. Label facts [FACT], calculations [CALCULATION], assumptions [ASSUMPTION], and recommendations [RECOMMENDATION].',
                AiPrompt::task($task, $context, $question),
                [
                    'task' => $task,
                    'max_tokens' => 900,
                    'context' => 'copilot:'.$task,
                    'ttl' => 6 * 3600,
                ]
            );

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

            AuditLogger::log('ai_copilot', 'ai', null, null, [
                'run_id' => $run->id,
                'task' => $task,
                'cached' => $result['cached'],
            ]);

            return response()->json([
                'content' => $result['content'],
                'mode' => 'ai',
                'cached' => $result['cached'],
                'provider' => $result['provider'],
                'run_id' => $run->id,
            ]);
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);

            // Provide a helpful local fallback response
            $localContext = $this->context->receivablesPayables()."\n".$this->context->inventory();
            $fallbackContent = "AI analysis is not available right now. Here is a summary from your ERP data:\n\n"
                ."To enable AI features, set an API key (NVIDIA_API_KEY or RAPIDAPI_KEY) in your .env file "
                ."and run: php artisan config:clear\n\n"
                ."**Your Current Business Data:**\n".$localContext;

            return response()->json([
                'content' => $fallbackContent,
                'mode' => 'local_fallback',
                'cached' => false,
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveDomain(string $question): array
    {
        $q = mb_strtolower($question);

        if (preg_match('/invoice|receivable|payable|overdue|payment|finance|cash|money/i', $q)) {
            return ['finance_analysis', $this->context->receivablesPayables()];
        }

        if (preg_match('/stock|inventory|product|reorder|warehouse/i', $q)) {
            return ['inventory_analysis', $this->context->inventory()];
        }

        if (preg_match('/supplier|procurement|rfq|purchase|offer|quote/i', $q)) {
            return ['procurement_analysis', $this->context->procurement()];
        }

        // Complex, high-value reasoning -> routed to Claude by the gateway.
        if (preg_match('/what should i do today|top priorities|prioritize my day|plan my day|daily plan/i', $q)) {
            return ['daily_priorities', $this->context->daily()];
        }

        if (preg_match('/executive review|business review|overall health|strategy|strategic|recommend|should (we|i)|decide|complex/i', $q)) {
            return ['strategic_recommendation', $this->context->executive()];
        }

        return ['business_summary', $this->context->receivablesPayables()."\n".$this->context->inventory()];
    }
}
