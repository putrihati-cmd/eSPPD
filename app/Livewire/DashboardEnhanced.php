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
