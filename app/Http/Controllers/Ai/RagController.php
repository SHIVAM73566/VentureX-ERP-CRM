<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Models\RagDocument;
use App\Models\RagPipeline;
use App\Models\RagQueryLog;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Rag\RagPipelineRegistry;
use App\Services\Rag\RagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

/**
 * Enterprise RAG workspace.
 *
 * Exposes the 80 registered retrieval-augmented generation pipelines through
 * a single dashboard: browse by domain, run a question against a pipeline and
 * (re)index source records. Every pipeline answers locally even without an
 * AI provider configured.
 */
class RagController extends Controller
{
    public function __construct(protected RagService $rag) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AiRun::class);

        $domain = $request->string('domain')->toString() ?: null;
        $pipelines = $this->rag->pipelines($domain);

        $domains = $this->rag->domains();
        $stats = $this->rag->stats();

        $recentQueries = RagQueryLog::query()
            ->ofCompany()
            ->with('pipeline')
            ->latest()
            ->limit(10)
            ->get();

        $selected = $pipelines->firstWhere('key', $request->string('pipeline')->toString())
            ?? $pipelines->first();

        return view('ai.rag', [
            'pipelines' => $pipelines,
            'domains' => $domains,
            'stats' => $stats,
            'recentQueries' => $recentQueries,
            'selected' => $selected,
            'selectedDomain' => $domain,
            'domainLabels' => RagPipelineRegistry::domainLabels(),
        ]);
    }

    public function tour(): View
    {
        $this->authorize('viewAny', AiRun::class);

        return view('ai.rag-tour', [
            'stats' => $this->rag->stats(),
            'domains' => $this->rag->domains(),
            'domainLabels' => RagPipelineRegistry::domainLabels(),
            'definitions' => RagPipelineRegistry::definitions(),
        ]);
    }

    public function query(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AiRun::class);

        $validated = $request->validate([
            'pipeline' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:2000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
            'use_ai' => ['nullable', 'boolean'],
        ]);

        try {
            $result = $this->rag->answer($validated['pipeline'], $validated['question'], [
                'limit' => $validated['limit'] ?? 8,
                'use_ai' => $request->boolean('use_ai'),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'The pipeline could not answer right now. The issue was logged for self-healing.',
            ], 500);
        }

        return response()->json(['success' => true] + $result);
    }

    public function sync(Request $request): JsonResponse
    {
        $this->authorize('create', AiRun::class);

        $validated = $request->validate([
            'pipeline' => ['nullable', 'string', 'max:100'],
        ]);

        $companyId = CompanyContext::id();

        try {
            if (! empty($validated['pipeline'])) {
                $indexed = $this->rag->syncPipeline($validated['pipeline'], $companyId);
                $message = "Indexed {$indexed} records for {$validated['pipeline']}.";
            } else {
                $result = $this->rag->syncAll($companyId);
                $indexed = array_sum($result);
                $message = 'Indexed '.$indexed.' records across '.count($result).' pipelines.';
            }
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Indexing failed. The issue was logged for self-healing.',
            ], 500);
        }

        AuditLogger::log('rag_index', 'ai', null, null, [
            'pipeline' => $validated['pipeline'] ?? 'all',
            'indexed' => $indexed,
            'company_id' => $companyId,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'indexed' => $indexed,
            'stats' => $this->rag->stats(),
        ]);
    }

    /**
     * Lightweight endpoint used by the dashboard to refresh per-pipeline
     * document counts without a full page reload.
     */
    public function pipeline(RagPipeline $pipeline): JsonResponse
    {
        $this->authorize('viewAny', AiRun::class);

        return response()->json([
            'success' => true,
            'pipeline' => [
                'key' => $pipeline->key,
                'name' => $pipeline->name,
                'domain' => $pipeline->domain,
                'documents' => RagDocument::query()->where('pipeline_id', $pipeline->id)->count(),
                'confidence' => (float) $pipeline->confidence,
                'weight' => (float) $pipeline->weight,
                'last_indexed_at' => $pipeline->last_indexed_at?->toIso8601String(),
                'last_trained_at' => $pipeline->last_trained_at?->toIso8601String(),
            ],
        ]);
    }
}
