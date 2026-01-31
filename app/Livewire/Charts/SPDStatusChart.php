<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use App\Models\SPD;
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
        $query = SPD::query();
        
        // Filter by user role
        if ($this->userRole !== 'admin') {
            $query->where('user_id', Auth::id());
        }
        
        $this->statusData = [
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
        ];
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.charts.spd-status-chart');
    }
}
