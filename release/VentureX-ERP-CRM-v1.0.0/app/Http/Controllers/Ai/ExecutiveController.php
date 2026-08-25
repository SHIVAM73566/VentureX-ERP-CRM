<?php

namespace App\Http\Controllers\Ai;

use App\Services\Ai\AiPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Executive-level analyses for the AI Gateway: the Executive Business Review
 * and the "What Should I Do Today?" daily copilot.
 *
 * All business facts are computed locally from the database FIRST; only the
 * summarized facts are sent to the (specialist) AI provider. If every provider
 * fails, the local facts are returned unchanged so the executive still gets
 * value without depending on an AI provider.
 */
class ExecutiveController extends AiActionController
{
    public function review(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $facts = $this->context->executive();

        return $this->respondWithLocalFallback($request, 'executive_review', 'company:executive-review', $facts, function () use ($facts) {
            return $this->gateway->chat(
                AiPrompt::system('executive business advisor')
                    .'Produce an Executive Business Review for a human executive. Use ONLY the facts provided. Output sections: '
                    .'Executive summary; Top 5 problems; Top 5 opportunities; Risks (financial / sales / inventory / '
                    .'procurement / customer); Priorities; Next 7 actions. Label each with [FACT], [CALCULATION], '
                    .'[ASSUMPTION] or [RECOMMENDATION]. Never present assumptions as facts.',
                AiPrompt::task('executive_review', $facts, 'Generate the executive business review.'),
                ['task' => 'executive_review', 'max_tokens' => 2400, 'context' => 'company:executive-review']
            );
        });
    }

    public function daily(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $facts = $this->context->daily();

        return $this->respondWithLocalFallback($request, 'daily_priorities', 'company:daily-priorities', $facts, function () use ($facts) {
            return $this->gateway->chat(
                AiPrompt::system('daily business copilot')
                    .'Using ONLY the facts provided, produce the top 5 priorities for today. For each priority give the '
                    .'reason, the impact, a recommended action, and the source data it is based on. Label each with '
                    .'[FACT], [CALCULATION], [ASSUMPTION] or [RECOMMENDATION]. Prioritize by business impact.',
                AiPrompt::task('daily_priorities', $facts, 'What should I do today?'),
                ['task' => 'daily_priorities', 'max_tokens' => 2000, 'context' => 'company:daily-priorities']
            );
        });
    }
}
