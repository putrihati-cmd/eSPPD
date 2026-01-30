<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\EmployeesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class ImportDosen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:dosen {file? : The path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dosen/pegawai data from Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?: base_path('md/DatabaseDosen.xlsx');

        if (!File::exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Importing data from: {$filePath}");

        $import = new EmployeesImport();
        
        try {
            Excel::import($import, $filePath);
            
            $this->table(
                ['Status', 'Count'],
                [
                    ['Imported (New)', $import->getImportedCount()],
                    ['Updated', $import->getUpdatedCount()],
                    ['Failed', count($import->getFailed())],
                ]
            );

            if (count($import->getFailed()) > 0) {
                if ($this->confirm('Show failed details?')) {
                    $this->table(['Row', 'NIP', 'Error'], $import->getFailed());
                }
            }

            $this->info('Import completed successfully!');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
