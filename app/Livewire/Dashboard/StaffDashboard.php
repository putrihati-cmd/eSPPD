<?php

namespace App\Livewire\Dashboard;

use App\Models\Sppd;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class StaffDashboard extends Component
{
    public $recentSppds;
    public $stats;

    public function mount()
    {
        if (Auth::user()->employee->approval_level !== 1) {
            abort(403, 'Akses tidak diizinkan untuk level ini.');
        }
        $employeeNip = Auth::user()->employee->nip;
        $this->recentSppds = Sppd::where('employee_nip', $employeeNip)
            ->latest()
            ->take(5)
            ->get();
        $this->stats = [
            'total' => Sppd::where('employee_nip', $employeeNip)->count(),
            'pending' => Sppd::where('employee_nip', $employeeNip)->where('status', 'pending')->count(),
            'approved' => Sppd::where('employee_nip', $employeeNip)->where('status', 'approved')->count(),
            'rejected' => Sppd::where('employee_nip', $employeeNip)->where('status', 'rejected')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.staff');
    }
}
