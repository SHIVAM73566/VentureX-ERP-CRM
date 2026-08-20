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

            return response()->json(['error' => 'Unable to generate insights at this time.'], 502);
        }
    }
}
