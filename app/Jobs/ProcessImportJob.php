<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\Import\ImportProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $mappings
     */
    public function __construct(
        protected int $importId,
        protected array $rows,
        protected array $mappings,
        protected int $companyId,
    ) {}

    public function handle(ImportProcessor $processor): void
    {
        try {
            $import = Import::find($this->importId);

            if (! $import) {
                Log::warning('ProcessImportJob: import no longer exists.', ['import_id' => $this->importId]);

                return;
            }

            $result = $processor->execute($import, $this->rows, $this->mappings, $this->companyId);

            Log::info('ProcessImportJob completed.', [
                'import_id' => $this->importId,
                'created' => $result['created'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'failed' => $result['failed'] ?? 0,
            ]);
        } catch (Throwable $e) {
            Log::error('ProcessImportJob failed.', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
