<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SPD;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardStaff extends Component
{
    public $totalSpds = 0;
    public $pendingSpds = 0;
    public $approvedSpds = 0;
    public $rejectedSpds = 0;
    public $recentSpds = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();

        // Total SPDs created by user
        $this->totalSpds = SPD::where('user_id', $userId)->count();

        // SPDs by status
        $this->pendingSpds = SPD::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $this->approvedSpds = SPD::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        $this->rejectedSpds = SPD::where('user_id', $userId)
            ->where('status', 'rejected')
            ->count();

        // Recent SPDs
        $this->recentSpds = SPD::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.staff-enhanced');
    }
}
