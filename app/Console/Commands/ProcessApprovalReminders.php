<?php

namespace App\Console\Commands;

use App\Models\Approval;
use App\Models\Spd;
use App\Notifications\SppdApprovalNotification;
use App\Services\ApprovalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessApprovalReminders extends Command
{
    protected $signature = 'approval:reminders';
    protected $description = 'Send reminders for pending approvals approaching deadline and escalate overdue ones';

    public function handle(ApprovalService $approvalService): int
    {
        $this->info('Processing approval reminders...');

        // Find approvals pending more than 24 hours (reminder)
        $pendingReminders = Approval::with(['spd', 'approver'])
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->where('created_at', '>=', now()->subHours(48))
            ->whereNull('reminded_at')
            ->get();

        foreach ($pendingReminders as $approval) {
            if ($approval->approver) {
                $approval->approver->notify(new SppdApprovalNotification($approval->spd, 'reminder'));
                $approval->update(['reminded_at' => now()]);
                $this->line("Reminder sent for SPPD: {$approval->spd->spd_number}");
            }
        }
        $this->info("Sent {$pendingReminders->count()} reminders.");

        // Find approvals pending more than 48 hours (escalate)
        $pendingEscalations = Approval::with(['spd'])
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHours(48))
            ->whereNull('escalated_at')
            ->get();

        foreach ($pendingEscalations as $approval) {
            $approvalService->escalate($approval->spd);
            $approval->update(['escalated_at' => now()]);
            $this->line("Escalated SPPD: {$approval->spd->spd_number}");
        }
        $this->info("Escalated {$pendingEscalations->count()} approvals.");

        // Log summary
        Log::info('Approval reminders processed', [
            'reminders_sent' => $pendingReminders->count(),
            'escalations' => $pendingEscalations->count(),
        ]);

        return Command::SUCCESS;
    }
}
