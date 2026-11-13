<?php

namespace App\Console\Commands;

use App\Jobs\SelfTrainingPipeline;
use App\Services\Rag\RagPipelineRegistry;
use App\Services\Rag\RagService;
use App\Services\Rag\VectorStoreService;
use Illuminate\Console\Command;

class RagSyncCommand extends Command
{
    protected $signature = 'rag:sync
        {--pipeline= : Only index a single pipeline key}
        {--company= : Restrict indexing to one company id}
        {--train : Run the self-training pass after indexing}';

    protected $description = 'Register and index the enterprise RAG pipelines';

    public function handle(RagService $rag, VectorStoreService $store): int
    {
        $registered = $store->ensurePipelines();
        $this->info("Registered {$registered} RAG pipelines (".RagPipelineRegistry::count().' defined).');

        $companyId = $this->option('company') !== null ? (int) $this->option('company') : null;
        $pipeline = $this->option('pipeline');

        if ($pipeline) {
            $indexed = $rag->syncPipeline((string) $pipeline, $companyId);
            $this->info("Indexed {$indexed} documents for [{$pipeline}].");
        } else {
            $results = $rag->syncAll($companyId);
            $this->info('Indexed '.array_sum($results).' documents across '.count($results).' pipelines.');
        }

        $store->reindexMissingEmbeddings();

        if ($this->option('train')) {
            dispatch_sync(new SelfTrainingPipeline);
            $this->info('Self-training pass completed.');
        }

        $stats = $rag->stats();
        $this->table(
            ['Pipelines', 'Active', 'Documents', 'Avg confidence'],
            [[$stats['pipelines'], $stats['active_pipelines'], $stats['documents'], number_format($stats['avg_confidence'] * 100, 2).'%']]
        );

        return self::SUCCESS;
    }
}
