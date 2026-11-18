<?php

namespace App\Jobs;

use App\Models\ExportRequest;
use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $exportRequestId) {}

    public function handle(ExportService $exports): void
    {
        try {
            $export = ExportRequest::find($this->exportRequestId);

            if (! $export) {
                Log::warning('GenerateExportJob: export request no longer exists.', ['export_request_id' => $this->exportRequestId]);

                return;
            }

            $exports->generate($export);
        } catch (Throwable $e) {
            Log::error('GenerateExportJob failed.', [
                'export_request_id' => $this->exportRequestId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
