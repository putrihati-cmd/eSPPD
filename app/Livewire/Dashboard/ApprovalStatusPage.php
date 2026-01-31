<?php

namespace App\Livewire\Dashboard;

use App\Models\Spd;
use App\Models\Approval;
use App\Models\ApprovalDelegation;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ApprovalStatusPage extends Component
{
    public ?int $selectedSpdId = null;
    public string $approvalStatus = ''; // pending, approved, rejected, all
    public string $searchQuery = '';

    #[Computed]
    public function pendingApprovals()
    {
        return Approval::query()
            ->with(['spd', 'approver'])
            ->where('status', 'pending')
            ->when($this->searchQuery, fn($q) =>
                $q->whereHas('spd', fn($sq) =>
                    $sq->where('spt_number', 'like', "%{$this->searchQuery}%")
                      ->orWhere('spd_number', 'like', "%{$this->searchQuery}%")
                )
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function recentApprovals()
    {
        return Approval::query()
            ->with(['spd', 'approver'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function stats()
    {
        $userId = auth()->id();
        return [
            'pending' => Approval::where('status', 'pending')
                ->whereHas('spd', fn($q) => $q->where('user_id', $userId))
                ->count(),
            'approved' => Approval::where('status', 'approved')
                ->whereHas('spd', fn($q) => $q->where('user_id', $userId))
                ->count(),
            'rejected' => Approval::where('status', 'rejected')
                ->whereHas('spd', fn($q) => $q->where('user_id', $userId))
                ->count(),
        ];
    }

    public function selectSpd(int $spdId): void
    {
        $this->selectedSpdId = $spdId;
    }

    public function render()
    {
        return view('livewire.dashboard.approval-status-page');
    }
}
