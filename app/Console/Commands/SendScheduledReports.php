<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Exports\SppdDataExport;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-scheduled';
    protected $description = 'Send scheduled reports to configured recipients';

    public function handle(): int
    {
        $this->info('Processing scheduled reports...');

        $reports = ScheduledReport::where('is_active', true)
            ->where('next_run_at', '<=', now())
            ->get();

        foreach ($reports as $report) {
            $this->processReport($report);
        }

        $this->info("Processed {$reports->count()} scheduled reports.");

        return Command::SUCCESS;
    }

    protected function processReport(ScheduledReport $report): void
    {
        try {
            // Generate report based on configuration
            $filters = $report->filters ?? [];
            
            // Adjust date filters based on frequency
            if ($report->frequency === 'weekly') {
                $filters['from_date'] = now()->subWeek()->format('Y-m-d');
                $filters['to_date'] = now()->format('Y-m-d');
            } elseif ($report->frequency === 'monthly') {
                $filters['from_date'] = now()->subMonth()->format('Y-m-d');
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $export = new SppdDataExport($filters);
            $filename = "Report_{$report->name}_" . now()->format('Y-m-d') . '.xlsx';
            $path = storage_path("app/temp/{$filename}");

            Excel::store($export, "temp/{$filename}");

            // Send email to recipients
            foreach ($report->recipients as $email) {
                Mail::raw(
                    "Laporan {$report->name} terlampir. Periode: {$filters['from_date']} - {$filters['to_date']}",
                    function ($message) use ($email, $report, $path, $filename) {
                        $message->to($email)
                            ->subject("Laporan Otomatis: {$report->name}")
                            ->attach($path, ['as' => $filename]);
                    }
                );
            }

            // Update next run time
            $nextRun = match($report->frequency) {
                'daily' => now()->addDay(),
                'weekly' => now()->addWeek(),
                'monthly' => now()->addMonth(),
                default => now()->addDay(),
            };
            $report->update([
                'last_run_at' => now(),
                'next_run_at' => $nextRun,
            ]);

            // Cleanup
            @unlink($path);

            $this->line("Sent report: {$report->name}");

        } catch (\Exception $e) {
            $this->error("Failed to send report {$report->name}: {$e->getMessage()}");
        }
    }
}
