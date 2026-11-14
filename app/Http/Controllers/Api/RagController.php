<?php

namespace App\Http\Controllers\Api;

use App\Models\AiRun;
use App\Services\Rag\RagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

/**
 * REST surface for the 84 enterprise RAG pipelines.
 */
class RagController extends ApiController
{
    public function __construct(protected RagService $rag) {}

    public function pipelines(Request $request): JsonResponse
    {
        if (! $request->user()?->can('viewAny', AiRun::class)) {
            return $this->errorResponse('You are not authorised to use RAG pipelines.', 403);
        }

        $domain = $request->string('domain')->toString() ?: null;
        $pipelines = $this->rag->pipelines($domain);

        return $this->successResponse([
            'count' => $pipelines->count(),
            'domains' => $this->rag->domains(),
            'pipelines' => $pipelines->map(fn ($pipeline) => [
                'key' => $pipeline->key,
                'name' => $pipeline->name,
                'domain' => $pipeline->domain,
                'description' => $pipeline->description,
                'retrieval_strategy' => $pipeline->retrieval_strategy,
                'confidence' => (float) $pipeline->confidence,
                'weight' => (float) $pipeline->weight,
                'document_count' => (int) $pipeline->document_count,
                'is_active' => (bool) $pipeline->is_active,
                'last_indexed_at' => $pipeline->last_indexed_at?->toIso8601String(),
                'last_trained_at' => $pipeline->last_trained_at?->toIso8601String(),
            ])->values(),
        ], 'RAG pipelines retrieved');
    }

    public function query(Request $request): JsonResponse
    {
        if (! $request->user()?->can('viewAny', AiRun::class)) {
            return $this->errorResponse('You are not authorised to use RAG pipelines.', 403);
        }

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
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('The pipeline could not answer right now.', 500);
        }

        return $this->successResponse($result, 'RAG answer generated');
    }

    public function sync(Request $request): JsonResponse
    {
        if (! $request->user()?->can('create', AiRun::class)) {
            return $this->errorResponse('You are not authorised to index RAG pipelines.', 403);
        }

        $validated = $request->validate([
            'pipeline' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            if (! empty($validated['pipeline'])) {
                $indexed = $this->rag->syncPipeline($validated['pipeline'], $this->companyId());
            } else {
                $indexed = array_sum($this->rag->syncAll($this->companyId()));
            }
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Indexing failed.', 500);
        }

        return $this->successResponse([
            'indexed' => $indexed,
            'stats' => $this->rag->stats(),
        ], 'RAG pipelines indexed');
    }

    public function stats(Request $request): JsonResponse
    {
        if (! $request->user()?->can('viewAny', AiRun::class)) {
            return $this->errorResponse('You are not authorised to use RAG pipelines.', 403);
        }

        return $this->successResponse($this->rag->stats(), 'RAG statistics retrieved');
    }
}
