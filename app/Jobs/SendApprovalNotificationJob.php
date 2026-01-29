<?php

namespace App\Jobs;

use App\Models\Approval;
use App\Notifications\ApprovalPendingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Send approval notification to approvers
 * Handles email and in-app notifications
 */
class SendApprovalNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 120;
    public $backoff = [10, 30, 60, 300, 900];

    public function __construct(
        private Approval $approval
    ) {
        $this->onQueue('notifications');
    }

    public function handle()
    {
        try {
            $approver = $this->approval->approver;

            if (!$approver) {
                Log::warning("Approver not found for approval {$this->approval->id}");
                return;
            }

            Log::info("Sending approval notification to user {$approver->id}");

            Notification::send($approver,
                new ApprovalPendingNotification($this->approval)
            );

            Log::info("Approval notification sent successfully");

        } catch (\Exception $e) {
            Log::error("Notification send error: {$e->getMessage()}");
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::warning("Notification send permanently failed", [
            'approval_id' => $this->approval->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
