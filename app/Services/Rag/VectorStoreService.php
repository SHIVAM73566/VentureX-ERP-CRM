<?php

namespace App\Services\Rag;

use App\Models\Company;
use App\Models\RagDocument;
use App\Models\RagPipeline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Portable vector store backed by the rag_documents table.
 *
 * Embeddings are JSON arrays; similarity is computed in PHP with cosine
 * distance. This keeps the feature fully functional on SQLite/MySQL without
 * requiring pgvector, Milvus or an external vector database.
 */
class VectorStoreService
{
    public function __construct(protected EmbeddingService $embeddings) {}

    /**
     * Seed/refresh the canonical 84 pipeline definitions without destroying
     * learned weights or confidence values.
     */
    public function ensurePipelines(): int
    {
        $count = 0;

        RagPipeline::withoutEvents(function () use (&$count) {
            foreach (RagPipelineRegistry::definitions() as $definition) {
                $pipeline = RagPipeline::firstOrNew(['key' => $definition['key']]);
                $existingConfig = is_array($pipeline->config) ? $pipeline->config : [];

                $pipeline->fill([
                    'company_id' => null,
                    'name' => $definition['name'],
                    'domain' => $definition['domain'],
                    'description' => $definition['description'],
                    'source_models' => $definition['source_models'],
                    'retrieval_strategy' => $definition['retrieval_strategy'],
                    'config' => array_merge($existingConfig, [
                        'keywords' => $definition['keywords'],
                    ]),
                ]);

                if (! $pipeline->exists) {
                    $pipeline->weight = 1.0;
                    $pipeline->confidence = 0.5;
                    $pipeline->is_active = true;
                }

                $pipeline->save();
                $count++;
            }
        });

        return $count;
    }

    /**
     * @return array<int, int|null>
     */
    public function companyIds(): array
    {
        $ids = Company::query()->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $ids === [] ? [null] : $ids;
    }

    /**
     * Index the source models for one pipeline.
     *
     * @return int Number of documents indexed.
     */
    public function syncPipeline(RagPipeline $pipeline, ?int $companyId = null, int $limit = 150): int
    {
        $companyIds = $companyId !== null ? [$companyId] : $this->companyIds();
        $indexed = 0;

        foreach ($companyIds as $company) {
            foreach ((array) ($pipeline->source_models ?? []) as $modelClass) {
                if (! is_string($modelClass) || ! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                    continue;
                }

                /** @var Model $prototype */
                $prototype = new $modelClass;
                $query = $prototype->newQuery();
                $table = $prototype->getTable();

                if (Schema::hasColumn($table, 'company_id')) {
                    if ($company === null) {
                        $query->whereNull('company_id');
                    } else {
                        $query->where('company_id', $company);
                    }
                }

                if (Schema::hasColumn($table, 'updated_at')) {
                    $query->orderByDesc('updated_at');
                } else {
                    $query->orderByDesc($prototype->getKeyName());
                }

                foreach ($query->limit($limit)->get() as $record) {
                    $this->upsertDocument($pipeline, $record, $company);
                    $indexed++;
                }
            }
        }

        $pipeline->forceFill([
            'document_count' => $pipeline->documents()->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))->count(),
            'last_indexed_at' => now(),
        ])->save();

        return $indexed;
    }

    /**
     * @return array<string, int>
     */
    public function syncAll(?int $companyId = null, int $limit = 150): array
    {
        $this->ensurePipelines();

        $result = [];
        foreach (RagPipeline::query()->where('is_active', true)->orderBy('domain')->orderBy('name')->get() as $pipeline) {
            $result[$pipeline->key] = $this->syncPipeline($pipeline, $companyId, $limit);
        }

        return $result;
    }

    public function upsertDocument(RagPipeline $pipeline, Model $record, ?int $companyId = null): RagDocument
    {
        $payload = $this->documentPayload($record);
        $embedding = $this->embeddings->embed($payload['title'].' '.$payload['content']);

        return RagDocument::updateOrCreate(
            [
                'pipeline_id' => $pipeline->id,
                'company_id' => $companyId,
                'source_type' => $record::class,
                'source_id' => $record->getKey(),
            ],
            [
                'title' => $payload['title'],
                'content' => $payload['content'],
                'embedding' => $embedding,
                'embedding_dim' => $this->embeddings->dimension(),
                'metadata' => $payload['metadata'],
                'confidence' => 0.6,
                'tokens' => $this->embeddings->tokenCount($payload['title'].' '.$payload['content']),
            ]
        );
    }

    /**
     * @return array<int, array{document: RagDocument, score: float}>
     */
    public function search(RagPipeline $pipeline, string $query, int $limit = 8, ?int $companyId = null): array
    {
        $queryVector = $this->embeddings->embed($query);
        $queryTokens = array_unique($this->embeddings->tokens($query));

        $documents = RagDocument::query()
            ->where('pipeline_id', $pipeline->id)
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id');
                if ($companyId !== null) {
                    $q->orWhere('company_id', $companyId);
                }
            })
            ->limit(750)
            ->get();

        $scored = [];
        foreach ($documents as $document) {
            $embedding = is_array($document->embedding) ? $document->embedding : null;
            if ($embedding === null) {
                $embedding = $this->embeddings->embed($document->title.' '.$document->content);
            }

            $score = $this->embeddings->similarity($queryVector, $embedding);

            // Lightweight lexical boost keeps exact identifiers (invoice
            // numbers, supplier names) prominent in the ranking.
            if ($queryTokens !== []) {
                $haystack = mb_strtolower($document->title.' '.$document->content);
                $hits = 0;
                foreach ($queryTokens as $token) {
                    if (mb_strpos($haystack, $token) !== false) {
                        $hits++;
                    }
                }
                $score += ($hits / count($queryTokens)) * 0.15;
            }

            if ($score <= 0.01) {
                continue;
            }

            $scored[] = [
                'document' => $document,
                'score' => round(min(1.0, $score), 6),
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, max(1, $limit));
    }

    public function reindexMissingEmbeddings(int $limit = 500): int
    {
        $updated = 0;

        RagDocument::query()
            ->whereNull('embedding')
            ->limit($limit)
            ->get()
            ->each(function (RagDocument $document) use (&$updated) {
                $document->forceFill([
                    'embedding' => $this->embeddings->embed($document->title.' '.$document->content),
                    'embedding_dim' => $this->embeddings->dimension(),
                ])->save();
                $updated++;
            });

        return $updated;
    }

    /**
     * @return array{title: string, content: string, metadata: array<string, mixed>}
     */
    public function documentPayload(Model $record): array
    {
        $sensitive = [
            'password', 'password_hash', 'two_factor_secret', 'two_factor_recovery_codes',
            'remember_token', 'api_token', 'secret', 'token', 'encrypted_key', 'key_hash',
            'credit_card', 'ssn', 'aadhaar', 'pan', 'bank_account', 'iban', 'swift_code',
        ];

        $attributes = $record->getAttributes();
        $lines = [];
        $metadata = [
            'model' => $record::class,
            'id' => $record->getKey(),
        ];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $sensitive, true)) {
                continue;
            }

            $formatted = $this->stringify($value);
            if ($formatted === '') {
                continue;
            }

            $lines[] = str_replace('_', ' ', ucfirst($key)).': '.$formatted;

            if (count($lines) >= 40) {
                break;
            }
        }

        $title = $this->titleFor($record, $attributes);
        $content = mb_substr($title."\n".implode("\n", $lines), 0, 6000);

        return [
            'title' => $title,
            'content' => $content,
            'metadata' => $metadata,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function titleFor(Model $record, array $attributes): string
    {
        foreach (['name', 'title', 'invoice_number', 'shipment_number', 'container_number',
            'purchase_order_number', 'quotation_number', 'order_number', 'sku', 'code',
            'email', 'key'] as $field) {
            if (! empty($attributes[$field]) && is_scalar($attributes[$field])) {
                return class_basename($record).' '.$attributes[$field];
            }
        }

        return class_basename($record).' #'.$record->getKey();
    }

    protected function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if (is_array($value)) {
            return mb_substr((string) json_encode($value), 0, 500);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            return '';
        }

        return mb_substr((string) $value, 0, 500);
    }
}
