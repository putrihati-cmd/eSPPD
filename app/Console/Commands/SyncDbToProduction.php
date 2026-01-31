<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class SyncDbToProduction extends Command
{
    protected $signature = 'db:sync-to-production {--dry-run : Preview sync without applying}';
    protected $description = 'Sync all database tables from local to production';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        try {
            // Get production server details
            $productionHost = env('PRODUCTION_HOST', '192.168.1.27');
            $productionUser = env('PRODUCTION_USER', 'tholib_server');
            $productionDbHost = env('PRODUCTION_DB_HOST', 'localhost');
            $productionDb = env('PRODUCTION_DB_NAME', 'esppd_production');
            $productionDbUser = env('PRODUCTION_DB_USER', 'postgres');
            $productionDbPass = env('PRODUCTION_DB_PASSWORD', '');

            // Local database
            $localDb = env('DB_DATABASE');
            $localUser = env('DB_USERNAME');
            $localPass = env('DB_PASSWORD');
            $localHost = env('DB_HOST', 'localhost');

            $this->info('🔄 Starting database sync: local → production');
            $this->line("   Local: $localDb @ $localHost");
            $this->line("   Production: $productionDb @ $productionDbHost");
            $this->newLine();

            if ($isDryRun) {
                $this->warn('⚠️  DRY RUN MODE - No changes will be applied');
                $this->newLine();
            }

            // Step 1: Export local database
            $this->info('[1/4] Exporting local database...');

            $dumpFile = storage_path("app/db_sync_" . now()->timestamp . ".sql");

            // Build pg_dump command for PostgreSQL
            $dumpCmd = "PGPASSWORD=\"{$localPass}\" pg_dump -h {$localHost} -U {$localUser} -d {$localDb} --no-owner --no-privileges > \"{$dumpFile}\"";

            $result = shell_exec($dumpCmd);

            if (!file_exists($dumpFile)) {
                $this->error('❌ Failed to export database');
                return 1;
            }

            $fileSize = filesize($dumpFile);
            $this->line("   ✓ Exported: " . $this->formatBytes($fileSize));

            // Step 2: Copy to production server
            $this->info('[2/4] Transferring to production server...');

            if ($isDryRun) {
                $this->line('   ✓ [DRY RUN] Would transfer via SCP');
            } else {
                $remoteFile = "/tmp/db_sync_" . now()->timestamp . ".sql";
                $scpCmd = "scp \"{$dumpFile}\" {$productionUser}@{$productionHost}:{$remoteFile}";

                exec($scpCmd, $output, $returnCode);

                if ($returnCode !== 0) {
                    $this->error('❌ SCP transfer failed');
                    @unlink($dumpFile);
                    return 1;
                }

                $this->line("   ✓ Transferred to: {$remoteFile}");

                // Step 3: Restore on production
                $this->info('[3/4] Importing on production server...');

                $sshCmd = "ssh {$productionUser}@{$productionHost} \"";
                $sshCmd .= "PGPASSWORD='{$productionDbPass}' psql -h {$productionDbHost} -U {$productionDbUser} -d {$productionDb} < {$remoteFile} && ";
                $sshCmd .= "rm {$remoteFile}";
                $sshCmd .= "\"";

                exec($sshCmd, $output, $returnCode);

                if ($returnCode !== 0) {
                    $this->error('❌ Import on production failed');
                    $this->error('Output: ' . implode("\n", $output));
                    @unlink($dumpFile);
                    return 1;
                }

                $this->line("   ✓ Database imported successfully");

                // Step 4: Clear caches on production
                $this->info('[4/4] Clearing caches on production...');

                $cacheCmd = "ssh {$productionUser}@{$productionHost} \"";
                $cacheCmd .= "cd /var/www/esppd && ";
                $cacheCmd .= "php artisan optimize:clear > /dev/null 2>&1 && ";
                $cacheCmd .= "php artisan view:cache > /dev/null 2>&1";
                $cacheCmd .= "\"";

                exec($cacheCmd);

                $this->line("   ✓ Caches cleared on production");
            }

            // Cleanup local dump file
            @unlink($dumpFile);

            $this->newLine();
            $this->info('✅ Database sync completed successfully!');
            $this->line("   Timestamp: " . now()->format('Y-m-d H:i:s'));

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
