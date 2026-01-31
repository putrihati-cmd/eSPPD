<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Spd;
use Carbon\Carbon;

class DashboardApprover extends Component
{
    public $pendingApprovals = 0;
    public $approvedThisMonth = 0;
    public $rejectedThisMonth = 0;
    public $totalProcessed = 0;
    public $recentApprovals = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $user = auth()->user();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Pending approvals
        $this->pendingApprovals = Spd::where('status', 'pending_approval')
            ->where('approver_id', $user->id)
            ->count();

        // Approved this month
        $this->approvedThisMonth = Spd::where('status', 'approved')
            ->whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->where('approver_id', $user->id)
            ->count();

        // Rejected this month
        $this->rejectedThisMonth = Spd::where('status', 'rejected')
            ->whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->where('approver_id', $user->id)
            ->count();

        // Total processed this month
        $this->totalProcessed = $this->approvedThisMonth + $this->rejectedThisMonth;

        // Recent approvals
        $this->recentApprovals = Spd::where('approver_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.approver-enhanced');
    }
}
