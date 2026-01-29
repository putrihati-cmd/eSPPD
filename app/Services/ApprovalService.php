<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalDelegate;
use App\Models\ApprovalRule;
use App\Models\Spd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;


class ApprovalService
{
    /**
     * Process approval workflow for SPPD
     */
    public function process(Spd $spd, string $action, ?string $notes = null): bool
    {
        $currentApproval = $spd->getPendingApproval();

        if (!$currentApproval) {
            return false;
        }

        if ($action === 'approve') {
            $this->approve($currentApproval, $notes);
            $this->checkAndProceed($spd, $currentApproval);
        } elseif ($action === 'reject') {
            $this->reject($currentApproval, $notes);
            // Update with rejection_reason from ceking.md + rejection tracking from fitur.md
            $spd->update([
                'status' => 'rejected',
                'rejection_reason' => $notes,
                'current_approver_nip' => null,
                'rejected_at' => now(),
                'rejected_by' => $currentApproval->approver?->nip ?? auth()->user()?->employee?->nip,
                'previous_approver_nip' => $currentApproval->approver?->nip, // For resubmission
            ]);
        }

        return true;
    }

    /**
     * Approve an approval step
     */
    protected function approve(Approval $approval, ?string $notes = null): void
    {
        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Reject an approval step
     */
    protected function reject(Approval $approval, ?string $notes = null): void
    {
        $approval->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Check if all approvals done and proceed (enhanced for ceking.md + fitur.md)
     */
    protected function checkAndProceed(Spd $spd, ?Approval $lastApproval = null): void
    {
        $pendingCount = $spd->approvals()->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            // All approvals completed - Final approve (from ceking.md)
            // Generate nomor surat otomatis (dari fitur.md)
            $sptNumber = $spd->spt_number;
            
            if (empty($sptNumber)) {
                // Use NomorSuratService to generate with retry for race condition
                $nomorData = \App\Services\NomorSuratService::generateWithRetry(
                    config('esppd.unit_default'),
                    config('esppd.kode_bagian_default')
                );
                $sptNumber = $nomorData['nomor_lengkap'];
                
                Log::info("Generated nomor surat: {$sptNumber} for SPD {$spd->id}");
            }
            
            $spd->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $lastApproval?->approver_id,
                'current_approver_nip' => null,
                'spt_number' => $sptNumber, // Auto-generated nomor surat
            ]);
            
            Log::info("SPD {$spd->spd_number} finally approved with nomor: {$sptNumber}");
        } else {
            // Notify next approver and update current_approver_nip (from ceking.md)
            $nextApproval = $spd->getPendingApproval();
            if ($nextApproval) {
                // Update current_approver_nip for tracking
                $spd->update([
                    'current_approver_nip' => $nextApproval->approver?->nip,
                ]);
                $this->notify($nextApproval);
            }
        }
    }

    /**
     * Notify approver (with delegation check)
     */
    public function notify(Approval $approval): void
    {
        $approver = $approval->approver;
        
        // Check if there's active delegation
        $delegate = ApprovalDelegate::getDelegateFor($approver->id ?? null);
        $targetApprover = $delegate ?? $approver;

        if (!$targetApprover || !$targetApprover->user) {
            return;
        }

        // Send email notification
        $targetApprover->user->notify(
            new \App\Notifications\SppdApprovalNotification($approval->spd, 'pending')
        );
        
        // Log notification
        \Log::info("Notification sent to: {$targetApprover->name} for SPD: {$approval->spd->spd_number}");
    }


    /**
     * Escalate overdue approvals
     */
    public function escalate(): int
    {
        $deadlineHours = config('app.approval_deadline_hours', 48);
        $escalatedCount = 0;

        $overdueApprovals = Approval::where('status', 'pending')
            ->where('created_at', '<', now()->subHours($deadlineHours))
            ->with(['spd', 'approver'])
            ->get();

        foreach ($overdueApprovals as $approval) {
            // Find higher level approver or admin
            $nextApprover = $this->findEscalationTarget($approval);
            
            if ($nextApprover) {
                $approval->update([
                    'approver_id' => $nextApprover->id,
                    'escalated_at' => now(),
                ]);
                $this->notify($approval);
                $escalatedCount++;
            }
        }

        return $escalatedCount;
    }

    /**
     * Find escalation target for overdue approval
     */
    protected function findEscalationTarget(Approval $approval): ?\App\Models\Employee
    {
        // Try to find next level approver
        $nextRule = ApprovalRule::active()
            ->where('level', '>', $approval->level)
            ->orderBy('level')
            ->first();

        return $nextRule?->approver;
    }

    /**
     * Create approval chain for SPPD
     */
    public function createApprovalChain(Spd $spd): void
    {
        // Get applicable rules
        $rules = ApprovalRule::active()
            ->forUnit($spd->unit_id)
            ->orderBy('level')
            ->get();

        if ($rules->isEmpty()) {
            // Default: create single approval for supervisor
            Approval::create([
                'spd_id' => $spd->id,
                'approver_id' => $spd->employee->supervisor_id ?? null,
                'level' => 1,
                'status' => 'pending',
            ]);
        } else {
            foreach ($rules as $rule) {
                // Check threshold
                if ($rule->threshold_amount && $spd->estimated_cost < $rule->threshold_amount) {
                    continue;
                }

                Approval::create([
                    'spd_id' => $spd->id,
                    'approver_id' => $rule->approver_id ?? $spd->employee->supervisor_id,
                    'level' => $rule->level,
                    'status' => 'pending',
                ]);
            }
        }

        // Notify first approver
        $firstApproval = $spd->getPendingApproval();
        if ($firstApproval) {
            $this->notify($firstApproval);
        }
    }

    /**
     * Bulk approve multiple SPPDs
     */
    public function bulkApprove(array $spdIds, int $approverId, ?string $notes = null): array
    {
        $results = ['success' => 0, 'failed' => 0];

        foreach ($spdIds as $spdId) {
            $spd = Spd::find($spdId);
            
            if (!$spd) {
                $results['failed']++;
                continue;
            }

            $approval = $spd->approvals()
                ->where('status', 'pending')
                ->where('approver_id', $approverId)
                ->first();

            if ($approval) {
                $this->process($spd, 'approve', $notes);
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }
}
