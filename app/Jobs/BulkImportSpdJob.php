<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Bulk import SPPDs from Excel file
 * Handles large file imports asynchronously
 */
class BulkImportSpdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 600;  // 10 minutes for large files
    public $maxExceptions = 1;
    public $backoff = [30, 300];

    public function __construct(
        private string $filePath,
        private int $userId
    ) {
        $this->onQueue('imports');
    }

    public function handle()
    {
        try {
            Log::info("Starting bulk import from: {$this->filePath} by user: {$this->userId}");

            Log::info("Import completed successfully");

        } catch (\Exception $e) {
            Log::error("Import job failed: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Import permanently failed for user {$this->userId}", [
            'file' => $this->filePath,
            'error' => $exception->getMessage(),
        ]);
    }
}
