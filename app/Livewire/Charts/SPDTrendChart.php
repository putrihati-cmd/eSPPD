<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use App\Models\SPD;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SPDTrendChart extends Component
{
    public $chartData = [];
    public $chartLabels = [];
    public $userRole = '';

    public function mount()
    {
        $this->userRole = Auth::user()->roles->first()?->name ?? 'staff';
        $this->loadChartData();
    }

    public function loadChartData()
    {
        $months = 6; // Last 6 months
        $now = Carbon::now();
        
        // Initialize labels and data arrays
        $this->chartLabels = [];
        $approvedData = [];
        $pendingData = [];
        $rejectedData = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $this->chartLabels[] = $month->translatedFormat('M Y');
            
            $query = SPD::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month);
            
            // Filter by user role
            if ($this->userRole !== 'admin') {
                $query->where('user_id', Auth::id());
            }
            
            // Count by status
            $approved = (clone $query)->where('status', 'approved')->count();
            $pending = (clone $query)->where('status', 'pending')->count();
            $rejected = (clone $query)->where('status', 'rejected')->count();
            
            $approvedData[] = $approved;
            $pendingData[] = $pending;
            $rejectedData[] = $rejected;
        }
        
        $this->chartData = [
            'approved' => $approvedData,
            'pending' => $pendingData,
            'rejected' => $rejectedData,
        ];
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.charts.spd-trend-chart');
    }
}
