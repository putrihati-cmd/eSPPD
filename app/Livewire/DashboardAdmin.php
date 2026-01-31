<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Spd;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;

class DashboardAdmin extends Component
{
    public $totalUsers = 0;
    public $totalSpds = 0;
    public $pendingApprovals = 0;
    public $approvedThisMonth = 0;
    public $rejectedThisMonth = 0;
    public $totalBudget = 0;
    public $expandedSection = null;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Total users
        $this->totalUsers = User::count();

        // Total SPDs
        $this->totalSpds = Spd::count();

        // Pending approvals
        $this->pendingApprovals = Spd::where('status', 'pending_approval')->count();

        // Approved this month
        $this->approvedThisMonth = Spd::where('status', 'approved')
            ->whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->count();

        // Rejected this month
        $this->rejectedThisMonth = Spd::where('status', 'rejected')
            ->whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->count();

        // Total budget (sum of all budget allocations)
        $this->totalBudget = \App\Models\Budget::sum('amount') ?? 0;
    }

    public function toggleSection($section)
    {
        $this->expandedSection = $this->expandedSection === $section ? null : $section;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.admin-enhanced');
    }
}
