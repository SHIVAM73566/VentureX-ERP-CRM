<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiRouter;
use App\Services\Ai\AiUsageMonitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * AI usage dashboard — real data from the usage monitor + AiRun table.
 */
class AiUsageController extends Controller
{
    public function __construct(
        protected AiUsageMonitor $usage,
        protected AiRouter $router,
        protected AiQuotaService $quota,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AiRun::class);

        $stats = $this->usage->stats(30);

        $health = collect(array_keys(config('ai.providers', [])))
            ->mapWithKeys(fn ($provider) => [
                $provider => [
                    'configured' => $this->router->isConfigured($provider),
                    'healthy' => $this->router->isHealthy($provider),
                ],
            ])
            ->all();

        $today = AiRun::ofCompany()->whereDate('created_at', today())->count();
        $failed30 = AiRun::ofCompany()->where('status', 'failed')->where('created_at', '>=', now()->subDays(30))->count();
        $fallback30 = AiRun::ofCompany()
            ->whereNotNull('error')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $byProvider = AiRun::ofCompany()
            ->select('provider', DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('provider')
            ->pluck('total', 'provider')
            ->all();

        $byUser = AiRun::ofCompany()
            ->select('user_id', DB::raw('count(*) as total'))
            ->with('user:id,name')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->mapWithKeys(fn ($run) => [$run->user?->name ?? 'Deleted user' => $run->total])
            ->all();

        $recent = AiRun::ofCompany()
            ->with('user:id,name')
            ->latest()
            ->limit(12)
            ->get();

        return view('ai.usage', compact('stats', 'today', 'failed30', 'fallback30', 'byProvider', 'byUser', 'recent', 'health'));
    }

    public function quota(): JsonResponse
    {
        $this->authorize('viewAny', AiRun::class);

        return response()->json($this->quota->check());
    }
}
