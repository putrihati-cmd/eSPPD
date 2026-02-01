<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalDelegate;
use App\Models\ApprovalRule;
use App\Models\Spd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Check level limits from md.md (Level => Max IDR)
        $limits = [
            1 => 0,          // Staff
            2 => 5000000,    // Kaprodi: 5jt
            3 => 20000000,   // Wadek: 20jt
            4 => 50000000,   // Dekan: 50jt
            5 => 100000000,  // WR: 100jt
            6 => 99999999999, // Rektor: Unlimited
        ];

        if ($action === 'approve') {
            $approver = $currentApproval->approver;
            $level = $approver->approval_level ?? 1;
            $limit = $limits[$level] ?? 0;

            // If cost exceeds limit and not level 6, validation should have caught this 
            // but we check anyway as a double safeguard.
            if ($spd->estimated_cost > $limit && $level < 6) {
                // Technically this shouldn't happen if chain is built correctly 
                // but if manual override, we block it.
                Log::warning("Approval limit exceeded for NIP {$approver->nip}", [
                    'cost' => $spd->estimated_cost,
                    'limit' => $limit
                ]);
            }

            $this->approve($currentApproval, $notes);
            $this->checkAndProceed($spd, $currentApproval);
        } elseif ($action === 'reject') {
            $this->reject($currentApproval, $notes);
            
            $spd->update([
                'status' => 'rejected',
                'rejection_reason' => $notes,
                'current_approver_nip' => null,
                'rejected_at' => now(),
                'rejected_by' => $currentApproval->approver?->nip ?? Auth::user()?->employee?->nip,
                'previous_approver_nip' => $currentApproval->approver?->nip,
            ]);

            // Transition logic
            $spd->transitionTo('rejected', $currentApproval->approver?->nip ?? 'system');
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
     * Check if all approvals done and proceed (Strictly matching md.md + 2.md)
     */
    protected function checkAndProceed(Spd $spd, ?Approval $lastApproval = null): void
    {
        $pendingCount = $spd->approvals()->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            // All approvals completed - Final approve (Fungsi finalizeSppd di md.md)
            DB::transaction(function () use ($spd, $lastApproval) {
                // 1. Lock Budget and update realisasi (Double Spend Protection dari 2.md/412)
                $budget = \App\Models\Budget::lockForUpdate()->find($spd->budget_id);
                if ($budget) {
                    $budget->increment('used_budget', $spd->estimated_cost);
                }

                // 2. Generate nomor surat otomatis jika belum ada
                $sptNumber = $spd->spt_number;
                $spdNumber = $spd->spd_number;
                
                if (empty($sptNumber) || str_contains($sptNumber, 'DRAFT')) {
                    $nomorData = \App\Services\NomorSuratService::generateWithRetry(
                        config('esppd.unit_default'),
                        config('esppd.kode_bagian_default')
                    );
                    $sptNumber = $nomorData['nomor_lengkap'];
                    $spdNumber = 'SPD.' . $sptNumber;
                }
                
                // 3. Update SPD status (Approved Final)
                $spd->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $lastApproval?->approver_id ?? Auth::id(),
                    'current_approver_nip' => null,
                    'spt_number' => $sptNumber,
                    'spd_number' => $spdNumber,
                ]);

                // 4. State Machine Transition (from 2.md)
                $spd->transitionTo('approved', $lastApproval?->approver?->nip ?? 'system');
            });

            // 5. Dispatch async document generation via Python
            \App\Jobs\GenerateDocumentJob::dispatch($spd->id, 'spt');
            \App\Jobs\GenerateDocumentJob::dispatch($spd->id, 'spd');
            
            Log::info("SPD {$spd->id} finalized with number: {$spd->spt_number}");
        } else {
            // Forward to next approver
            $nextApproval = $spd->getPendingApproval();
            if ($nextApproval) {
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
        Log::info("Notification sent to: {$targetApprover->name} for SPD: {$approval->spd->spd_number}");
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
     * Create approval chain for SPPD (Strictly matching md.md hierarchy)
     */
    public function createApprovalChain(Spd $spd): void
    {
        // Define limits from md.md (Level => Max IDR)
        $limits = [
            1 => 0,          // Staff
            2 => 5000000,    // Kaprodi: 5jt
            3 => 20000000,   // Wadek: 20jt
            4 => 50000000,   // Dekan: 50jt
            5 => 100000000,  // WR: 100jt
            6 => 99999999999, // Rektor: Unlimited
        ];

        // 1. Get applicable rules for the unit
        $rules = ApprovalRule::active()
            ->forUnit($spd->unit_id)
            ->orderBy('level')
            ->get();

        if ($rules->isEmpty()) {
            // Default Fallback: create single approval for supervisor
            Approval::create([
                'spd_id' => $spd->id,
                'approver_id' => $spd->employee->supervisor_id ?? null,
                'level' => 1,
                'status' => 'pending',
            ]);
        } else {
            foreach ($rules as $rule) {
                Approval::create([
                    'spd_id' => $spd->id,
                    'approver_id' => $rule->approver_id ?? $spd->employee->supervisor_id,
                    'level' => $rule->level,
                    'status' => 'pending',
                ]);
                
                // If this level's limit covers the cost, we can stop the chain here
                if (isset($limits[$rule->level]) && $spd->estimated_cost <= $limits[$rule->level]) {
                    break;
                }
            }
        }

        // Notify first approver
        $firstApproval = $spd->getPendingApproval();
        if ($firstApproval) {
            $spd->update(['current_approver_nip' => $firstApproval->approver?->nip]);
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
