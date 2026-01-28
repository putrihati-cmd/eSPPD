<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempFiles extends Command
{
    protected $signature = 'esppd:cleanup-temp';
    protected $description = 'Clean up temporary files older than 24 hours';

    public function handle(): int
    {
        $this->info('Cleaning up temporary files...');

        $directories = [
            'temp',
            'imports',
            'excel/temp',
        ];

        $count = 0;

        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                continue;
            }

            $files = Storage::files($dir);

            foreach ($files as $file) {
                $lastModified = Storage::lastModified($file);
                
                // Delete files older than 24 hours
                if ($lastModified < now()->subHours(24)->timestamp) {
                    Storage::delete($file);
                    $count++;
                    $this->line("Deleted: $file");
                }
            }
        }

        $this->info("Cleanup complete. Deleted $count files.");

        return Command::SUCCESS;
    }
}
