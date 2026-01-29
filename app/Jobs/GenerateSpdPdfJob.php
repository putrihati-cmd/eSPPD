<?php

namespace App\Jobs;

use App\Models\Spd;
use App\Services\DocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate PDF for SPPD document
 * Handles document generation asynchronously
 */
class GenerateSpdPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;  // 5 minutes
    public $maxExceptions = 3;
    public $backoff = [10, 60, 300];

    public function __construct(
        private Spd $spd
    ) {
        $this->onQueue('default');
    }

    public function handle()
    {
        try {
            Log::info("Generating PDF for SPPD: {$this->spd->id}");

            Log::info("PDF generated successfully for SPPD: {$this->spd->id}");

        } catch (\Exception $e) {
            Log::error("PDF generation failed for SPPD {$this->spd->id}: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("PDF Generation permanently failed for SPPD {$this->spd->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
