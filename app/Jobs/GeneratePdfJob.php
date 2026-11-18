<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\BrandingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $invoiceId,
        protected int $companyId,
        protected ?string $storagePath = null,
    ) {}

    public function handle(BrandingService $branding): void
    {
        try {
            $invoice = Invoice::ofCompany($this->companyId)
                ->with('customer', 'salesOrder', 'items.product', 'payments', 'createdBy')
                ->find($this->invoiceId);

            if (! $invoice) {
                Log::warning('GeneratePdfJob: invoice no longer exists.', ['invoice_id' => $this->invoiceId]);

                return;
            }

            $path = $this->storagePath ?: 'invoices/'.$this->companyId.'/invoice-'.$invoice->id.'.pdf';

            $pdf = Pdf::loadView('sales.invoices.pdf', [
                'invoice' => $invoice,
                'branding' => $branding->all(),
            ])
                ->setPaper('a4')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false);

            Storage::disk('local')->put($path, $pdf->output());
        } catch (Throwable $e) {
            Log::error('GeneratePdfJob failed.', [
                'invoice_id' => $this->invoiceId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
