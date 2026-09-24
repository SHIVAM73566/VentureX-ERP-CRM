<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Services\Ai\AiException;
use App\Services\Ai\AiInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AI Business Insights — explicit generation only (never on page load).
 */
class AiInsightsController extends Controller
{
    public function __construct(protected AiInsightService $insights) {}

    public function generate(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');

        $this->authorize('viewAny', AiRun::class);

        try {
            $result = $this->insights->generate();

            return response()->json($result);
        } catch (AiException $e) {
            Log::warning('AI insights failed', ['error' => $e->getMessage()]);

            // Local fallback: compute insights directly from database business rules.
            $rules = $this->insights->rules();

            $lines = collect($rules)
                ->map(fn ($r) => '['.strtoupper((string) $r['tone']).'] '.$r['text'])
                ->implode("\n");

            $content = "AI-generated insights are not available right now. Here are rule-based insights computed from your ERP data:\n\n"
                .($lines !== '' ? $lines : 'No critical signals detected.')
                ."\n\n[TIP] To enable AI insights, connect an AI provider in **AI Settings** (".route('admin.ai-providers.setup').'). '
                .'No .env editing or config:clear is needed — ask an administrator if you cannot access the admin area.';

            return response()->json([
                'content' => $content,
                'generated_at' => now()->toIso8601String(),
                'provider' => 'local',
                'model' => 'business_rules',
                'cached' => false,
                'mode' => 'local_fallback',
                'insights' => $rules,
            ]);
        }
    }
}
