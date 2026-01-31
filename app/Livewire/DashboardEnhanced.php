<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Spd;
use App\Models\User;
use App\Services\DashboardCacheService;
use App\Services\SPDQueryOptimizer;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;

class DashboardEnhanced extends Component
{
    public $totalSpdThisMonth = 0;
    public $pendingApproval = 0;
    public $approvedSpd = 0;
    public $rejectedSpd = 0;
    public $recentSpds = [];
    public $userRole = '';
    public $expandedSection = null;

    #[On('refresh-dashboard')]
    public function mount()
    {
        $this->userRole = auth()->user()->role->name ?? 'employee';
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Use cache service for faster data retrieval
        $metrics = DashboardCacheService::getUserMetrics();
        $this->totalSpdThisMonth = $metrics['total'];
        $this->pendingApproval = $metrics['pending'];
        $this->approvedSpd = $metrics['approved'];
        $this->rejectedSpd = $metrics['rejected'];

        // Use query optimizer for recent SPDs
        $this->recentSpds = SPDQueryOptimizer::getRecentSpds(5);
    }
        $user = auth()->user();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Common stats for all users
        $this->totalSpdThisMonth = Spd::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->where('user_id', $user->id)
            ->count();

        // Pending approvals (for approvers)
        $this->pendingApproval = Spd::where('status', 'pending_approval')
            ->where('approver_id', $user->id)
            ->count();

        // Approved this month (for approvers)
        $this->approvedSpd = Spd::where('status', 'approved')
            ->whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->where('approver_id', $user->id)
            ->count();

        // Rejected this month (for approvers)
        $this->rejectedSpd = Spd::where('status', 'rejected')
            ->whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->where('approver_id', $user->id)
            ->count();

        // Recent SPDs
        $this->recentSpds = Spd::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($spd) => [
                'id' => $spd->id,
                'destination' => $spd->destination,
                'status' => $spd->status,
                'status_label' => $this->getStatusLabel($spd->status),
                'status_color' => $this->getStatusColor($spd->status),
                'start_date' => $spd->start_date->format('d M Y'),
                'end_date' => $spd->end_date->format('d M Y'),
            ])
            ->toArray();
    }

    public function toggleSection($section)
    {
        $this->expandedSection = $this->expandedSection === $section ? null : $section;
    }

    public function getStatusLabel($status)
    {
        return match ($status) {
            'draft' => 'Draf',
            'submitted' => 'Diajukan',
            'pending_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            default => $status,
        };
    }

    public function getStatusColor($status)
    {
        return match ($status) {
            'draft' => 'slate',
            'submitted' => 'blue',
            'pending_approval' => 'orange',
            'approved' => 'emerald',
            'rejected' => 'red',
            'completed' => 'teal',
            default => 'gray',
        };
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard-enhanced');
    }
}
