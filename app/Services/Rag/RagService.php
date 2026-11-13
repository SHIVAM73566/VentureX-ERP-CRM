<?php

namespace App\Services\Rag;

use App\Models\RagDocument;
use App\Models\RagPipeline;
use App\Models\RagQueryLog;
use App\Services\Ai\AiGateway;
use App\Services\CompanyContext;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

/**
 * Unified RAG query API used by the web dashboard, REST API and AI copilots.
 *
 * Every pipeline is queryable independently. Retrieval always runs locally
 * first; when an AI provider is configured the retrieved context can be sent
 * through the existing AiGateway, otherwise a local extractive answer is
 * returned. The pipelines therefore remain operational with zero API keys.
 */
class RagService
{
    public function __construct(
        protected VectorStoreService $store,
        protected AiGateway $gateway,
    ) {}

    public function ensurePipelines(): int
    {
        return $this->store->ensurePipelines();
    }

    /**
     * @return Collection<int, RagPipeline>
     */
    public function pipelines(?string $domain = null, bool $onlyActive = true): Collection
    {
        $this->ensurePipelines();

        return RagPipeline::query()
            ->when($domain, fn ($q) => $q->where('domain', $domain))
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('domain')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, array{label: string, pipelines: int, documents: int}>
     */
    public function domains(): array
    {
        $pipelines = $this->pipelines(null, false);
        $documents = RagDocument::query()
            ->selectRaw('pipeline_id, count(*) as total')
            ->groupBy('pipeline_id')
            ->pluck('total', 'pipeline_id');

        $domains = [];
        foreach (RagPipelineRegistry::domainLabels() as $key => $label) {
            $domainPipelines = $pipelines->where('domain', $key);
            $domains[$key] = [
                'label' => $label,
                'pipelines' => $domainPipelines->count(),
                'documents' => (int) $domainPipelines->sum(fn (RagPipeline $pipeline) => (int) ($documents[$pipeline->id] ?? 0)),
            ];
        }

        return $domains;
    }

    public function findByKey(string $key): RagPipeline
    {
        $this->ensurePipelines();

        $pipeline = RagPipeline::query()->where('key', $key)->first();

        if (! $pipeline) {
            throw new InvalidArgumentException("Unknown RAG pipeline [{$key}].");
        }

        return $pipeline;
    }

    /**
     * Run a retrieval-augmented answer for a pipeline.
     *
     * @param  array{limit?: int, use_ai?: bool, auto_index?: bool}  $options
     * @return array<string, mixed>
     */
    public function answer(string $pipelineKey, string $question, array $options = []): array
    {
        $started = microtime(true);
        $pipeline = $this->findByKey($pipelineKey);
        $limit = max(1, min(25, (int) ($options['limit'] ?? 8)));
        $companyId = CompanyContext::id();

        $matches = $this->store->search($pipeline, $question, $limit, $companyId);

        // Lazy first-run indexing: a brand-new install (or a pipeline that has
        // never been trained) still answers immediately instead of returning
        // "no documents".
        if ($matches === [] && ($options['auto_index'] ?? true)) {
            $this->store->syncPipeline($pipeline, $companyId);
            $matches = $this->store->search($pipeline, $question, $limit, $companyId);
        }

        $sources = array_map(fn (array $match) => [
            'id' => $match['document']->id,
            'title' => $match['document']->title,
            'source_type' => class_basename($match['document']->source_type),
            'source_id' => $match['document']->source_id,
            'score' => $match['score'],
            'snippet' => mb_substr(preg_replace('/\s+/', ' ', (string) $match['document']->content) ?? '', 0, 320),
        ], $matches);

        $mode = 'local';
        $answer = $this->localAnswer($pipeline, $question, $sources);

        if (($options['use_ai'] ?? false) && $sources !== [] && $this->gateway->isEnabled()) {
            try {
                $context = collect($sources)
                    ->map(fn (array $source, int $index) => '['.($index + 1).'] '.$source['title'].': '.$source['snippet'])
                    ->implode("\n");

                $result = $this->gateway->chat(
                    'You are the '.$pipeline->name.' retrieval assistant for an ERP. Answer only from the supplied records. '
                    .'Cite records as [1], [2]. If the records do not contain the answer, say so explicitly.',
                    "Question: {$question}\n\nRetrieved records:\n{$context}",
                    [
                        'task' => 'rag:'.$pipeline->key,
                        'max_tokens' => 900,
                        'context' => 'rag:'.$pipeline->key,
                        'ttl' => 3600,
                    ]
                );

                if (! empty($result['content'])) {
                    $answer = (string) $result['content'];
                    $mode = 'ai';
                }
            } catch (Throwable) {
                // Keep the local answer: RAG must never fail because a
                // provider is down or unconfigured.
                $mode = 'local';
            }
        }

        $latency = (int) round((microtime(true) - $started) * 1000);
        $topScore = $sources[0]['score'] ?? 0.0;

        $this->recordQuery($pipeline, $question, $mode, (float) $topScore, count($sources), $latency);
        $this->learn($pipeline, (float) $topScore, count($sources));

        return [
            'pipeline' => [
                'key' => $pipeline->key,
                'name' => $pipeline->name,
                'domain' => $pipeline->domain,
                'domain_label' => $pipeline->domainLabel(),
            ],
            'question' => $question,
            'answer' => $answer,
            'sources' => $sources,
            'mode' => $mode,
            'confidence' => round((float) $pipeline->confidence, 4),
            'latency_ms' => $latency,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $sources
     */
    protected function localAnswer(RagPipeline $pipeline, string $question, array $sources): string
    {
        if ($sources === []) {
            return "No indexed records were found for the {$pipeline->name} pipeline yet. "
                .'Open the pipeline and run "Index now", or wait for the nightly self-training job, then try again.';
        }

        $lines = [
            "{$pipeline->name} — {$pipeline->domainLabel()}",
            '',
            'Most relevant indexed records for: '.$question,
            '',
        ];

        foreach ($sources as $index => $source) {
            $lines[] = ($index + 1).'. '.$source['title'].' (match '.number_format((float) $source['score'] * 100, 1).'%)';
            $lines[] = '   '.$source['snippet'];
            $lines[] = '';
        }

        $lines[] = 'This answer was assembled locally from your ERP records. Connect an AI provider to generate a narrative summary.';

        return implode("\n", $lines);
    }

    protected function recordQuery(RagPipeline $pipeline, string $question, string $mode, float $topScore, int $resultCount, int $latency): void
    {
        try {
            RagQueryLog::create([
                'company_id' => CompanyContext::id(),
                'user_id' => auth()->id(),
                'pipeline_id' => $pipeline->id,
                'query' => mb_substr($question, 0, 2000),
                'mode' => $mode,
                'top_score' => $topScore,
                'result_count' => $resultCount,
                'latency_ms' => $latency,
            ]);
        } catch (Throwable) {
            // Analytics must never break an answer.
        }
    }

    /**
     * Small online-learning step: move retrieval confidence toward the
     * observed top score. The nightly SelfTrainingPipeline performs the
     * heavier batch update.
     */
    protected function learn(RagPipeline $pipeline, float $topScore, int $resultCount): void
    {
        if ($resultCount === 0) {
            return;
        }

        $confidence = (float) $pipeline->confidence;
        $pipeline->forceFill([
            'confidence' => round(($confidence * 0.9) + ($topScore * 0.1), 4),
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $pipelines = $this->pipelines(null, false);

        return [
            'pipelines' => $pipelines->count(),
            'active_pipelines' => $pipelines->where('is_active', true)->count(),
            'documents' => RagDocument::query()->count(),
            'domains' => $this->domains(),
            'avg_confidence' => round((float) $pipelines->avg('confidence'), 4),
            'last_trained_at' => $pipelines->max('last_trained_at'),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function syncAll(?int $companyId = null): array
    {
        return $this->store->syncAll($companyId);
    }

    public function syncPipeline(string $key, ?int $companyId = null): int
    {
        return $this->store->syncPipeline($this->findByKey($key), $companyId);
    }
}
