<?php

namespace App\Livewire\Approvals;

use App\Models\Approval;
use Livewire\Component;

class ApprovalIndex extends Component
{
    public function render()
    {
        $user = auth()->user();
        
        $approvals = Approval::with(['spd.employee', 'spd.unit', 'approver'])
            ->whereHas('spd', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->pending()
            ->latest()
            ->get();

        return view('livewire.approvals.approval-index', [
            'approvals' => $approvals,
        ])->layout('layouts.app', ['header' => 'Approval']);
    }
}
