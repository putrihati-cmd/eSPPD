<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use App\Models\SPD;
use App\Services\DashboardCacheService;
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
        // Use cache service for trend data
        $trendData = DashboardCacheService::getTrendData();

        $this->chartLabels = array_column($trendData, 'month');
        $this->chartData = [
            'approved' => array_column($trendData, 'approved'),
            'pending' => array_column($trendData, 'pending'),
            'rejected' => array_column($trendData, 'rejected'),
    }
}
