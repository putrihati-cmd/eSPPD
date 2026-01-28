<?php

namespace App\Jobs;

use App\Imports\SppdImport;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportSppdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $userId;

    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $import = new SppdImport();
        
        Excel::import($import, Storage::path($this->filePath));

        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ImportCompletedNotification(
                $import->getSuccessCount(),
                $import->getErrorCount(),
                $import->getImportErrors()
            ));
        }

        // Clean up temp file
        Storage::delete($this->filePath);
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ImportCompletedNotification(0, 0, [$exception->getMessage()]));
        }

        Storage::delete($this->filePath);
    }
}
