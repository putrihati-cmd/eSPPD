<?php

namespace App\Jobs;

use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate monthly reports
 * Handles large report generation asynchronously
 */
class GenerateMonthlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;  // 10 minutes
    public $backoff = [30, 300, 900];

    public function __construct(
        private int $unitId,
        private int $month,
        private int $year
    ) {
        $this->onQueue('reports');
    }

    public function handle(ReportService $service)
    {
        try {
            Log::info("Generating monthly report for unit {$this->unitId}, {$this->year}-{$this->month}");

            $report = $service->generateMonthlyReport(
                $this->unitId,
                $this->month,
                $this->year
            );

            Log::info("Report generated successfully");

            event(new \App\Events\ReportGenerated($this->unitId, $this->month, $this->year));

        } catch (\Exception $e) {
            Log::error("Report generation failed: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Report generation permanently failed", [
            'unit_id' => $this->unitId,
            'period' => "{$this->year}-{$this->month}",
            'error' => $exception->getMessage(),
        ]);
    }
}
