<?php

namespace App\Livewire\Approvals;

use App\Models\Approval;
use App\Models\Spd;
use Livewire\Component;

class ApprovalIndex extends Component
{
    public function approve($approvalId)
    {
        $approval = Approval::findOrFail($approvalId);
        
        // Verify user has permission
        if ($approval->approver_id !== auth()->user()->employee_id) {
            session()->flash('error', 'Anda tidak memiliki akses untuk approval ini.');
            return;
        }
        
        // Update approval status
        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
            'notes' => 'Disetujui'
        ]);
        
        // Check if this is the last approval level
        $spd = $approval->spd;
        $allApproved = $spd->approvals()->where('status', '!=', 'approved')->count() === 0;
        
        if ($allApproved) {
            $spd->update(['status' => 'approved']);
            
            // Update budget used
            if ($spd->budget_id) {
                $budget = $spd->budget;
                $budget->increment('used_budget', $spd->estimated_cost);
            }
        }
        
        session()->flash('message', 'SPD berhasil disetujui!');
    }
    
    public function reject($approvalId, $notes = null)
    {
        $approval = Approval::findOrFail($approvalId);
        
        // Verify user has permission
        if ($approval->approver_id !== auth()->user()->employee_id) {
            session()->flash('error', 'Anda tidak memiliki akses untuk approval ini.');
            return;
        }
        
        // Update approval status
        $approval->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'notes' => $notes ?? 'Ditolak'
        ]);
        
        // Update SPD status
        $approval->spd->update(['status' => 'rejected']);
        
        session()->flash('message', 'SPD berhasil ditolak.');
    }

    public function render()
    {
        $user = auth()->user();
        
        // Get approvals where current user is the approver
        $approvals = Approval::with(['spd.employee', 'spd.unit', 'approver'])
            ->where('approver_id', $user->employee_id)
            ->pending()
            ->latest()
            ->get();

        return view('livewire.approvals.approval-index', [
            'approvals' => $approvals,
        ])->layout('layouts.app', ['header' => 'Approval']);
    }
}
