<?php

namespace Database\Seeders;

use App\Services\Rag\VectorStoreService;
use Illuminate\Database\Seeder;

class RagPipelineSeeder extends Seeder
{
    /**
     * Register (or refresh) the 84 canonical enterprise RAG pipelines.
     * Learned weights and confidence values are preserved on re-run.
     */
    public function run(): void
    {
        $count = app(VectorStoreService::class)->ensurePipelines();

        $this->command?->info("RAG pipelines registered: {$count}");
    }
}
