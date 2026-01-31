<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use App\Models\SPD;
use App\Services\DashboardCacheService;
use Illuminate\Support\Facades\Auth;

class SPDStatusChart extends Component
{
    public $statusData = [];
    public $userRole = '';

    public function mount()
    {
        $this->userRole = Auth::user()->roles->first()?->name ?? 'staff';
        $this->loadStatusData();
    }

    public function loadStatusData()
    {
        // Use cache service for metrics
        $metrics = DashboardCacheService::getUserMetrics();

        $this->statusData = [
            'approved' => $metrics['approved'],
            'pending' => $metrics['pending'],
            'rejected' => $metrics['rejected'],
            'draft' => 0, // Can be added if needed
    }
}
