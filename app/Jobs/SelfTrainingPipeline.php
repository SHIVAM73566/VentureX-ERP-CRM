<?php

namespace App\Jobs;

use App\Models\RagPipeline;
use App\Models\RagQueryLog;
use App\Services\AuditLogger;
use App\Services\Rag\RagService;
use App\Services\Rag\VectorStoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Nightly self-training pipeline.
 *
 * Analyses the previous day of retrieval traffic and transaction patterns,
 * then adjusts each pipeline's weight and confidence so frequently useful
 * pipelines rank higher and stale ones decay. Also backfills missing
 * embeddings and warms pipelines that have never been indexed.
 *
 * The job is idempotent and safe to run manually: `php artisan rag:sync --train`.
 */
class SelfTrainingPipeline implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 900;

    public function handle(RagService $rag, VectorStoreService $store): void
    {
        $store->ensurePipelines();
        $store->reindexMissingEmbeddings();

        $since = now()->subDay();
        $traffic = RagQueryLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('pipeline_id, count(*) as queries, avg(top_score) as avg_score, avg(result_count) as avg_results')
            ->groupBy('pipeline_id')
            ->get()
            ->keyBy('pipeline_id');

        $totalQueries = max(1, (int) $traffic->sum('queries'));
        $trained = 0;
        $indexed = 0;

        foreach (RagPipeline::query()->where('is_active', true)->get() as $pipeline) {
            $row = $traffic->get($pipeline->id);

            $confidence = (float) $pipeline->confidence;
            $weight = (float) $pipeline->weight;

            if ($row) {
                $observed = (float) $row->avg_score;
                $usageShare = (int) $row->queries / $totalQueries;

                // Exponential moving average: 20% new evidence, 80% history.
                $confidence = ($confidence * 0.8) + ($observed * 0.2);

                // Weight rewards pipelines that are actually used, bounded so
                // one noisy day cannot dominate retrieval.
                $weight = ($weight * 0.85) + ((0.5 + $usageShare) * 0.15);
                $weight = max(0.25, min(2.0, $weight));
            } else {
                // No traffic: gently decay confidence so untested pipelines
                // stop outranking proven ones.
                $confidence = max(0.2, $confidence * 0.98);
            }

            $attributes = [
                'confidence' => round(max(0.05, min(0.99, $confidence)), 4),
                'weight' => round($weight, 4),
                'last_trained_at' => now(),
            ];

            // Warm pipelines that have never been indexed so the next user
            // query is fast. Failures are isolated per pipeline.
            if (! $pipeline->last_indexed_at) {
                try {
                    $indexed += $store->syncPipeline($pipeline);
                    $attributes['last_indexed_at'] = now();
                    $attributes['document_count'] = $pipeline->documents()->count();
                } catch (Throwable $e) {
                    Log::warning('rag.self_training.index_failed', [
                        'pipeline' => $pipeline->key,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $pipeline->forceFill($attributes)->save();
            $trained++;
        }

        AuditLogger::log('rag_self_training', 'ai', null, null, [
            'pipelines_trained' => $trained,
            'documents_indexed' => $indexed,
            'queries_analysed' => (int) $traffic->sum('queries'),
            'window' => $since->toIso8601String(),
        ]);
    }
}
