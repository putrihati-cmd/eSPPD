<?php

namespace App\Livewire\Dashboard;

use App\Models\Sppd;
use App\Models\SppdApproval;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class ApproverDashboard extends Component
{
    public $pendingApprovals;
    public $stats;
    public $userLevel;

    public function mount()
    {
        $user = Auth::user();
        $this->userLevel = $user->employee->approval_level;
        if ($this->userLevel < 2) {
            abort(403);
        }
        $this->loadPendingApprovals();
        $this->loadStats();
    }

    private function loadPendingApprovals()
    {
        $userNip = Auth::user()->employee->nip;
        $this->pendingApprovals = Sppd::where('current_approver_nip', $userNip)
            ->where('status', 'pending')
            ->with('employee')
            ->latest()
            ->get();
    }

    private function loadStats()
    {
        $userNip = Auth::user()->employee->nip;
        $this->stats = [
            'waiting_me' => Sppd::where('current_approver_nip', $userNip)
                ->where('status', 'pending')->count(),
            'approved_by_me' => \App\Models\SppdApproval::where('approver_nip', $userNip)
                ->where('status', 'approved')->count(),
            'rejected_by_me' => \App\Models\SppdApproval::where('approver_nip', $userNip)
                ->where('status', 'rejected')->count(),
        ];
    }

    public function render()
    {
        $view = match ($this->userLevel) {
            2 => 'livewire.dashboard.kaprodi',
            3 => 'livewire.dashboard.wadek',
            4 => 'livewire.dashboard.dekan',
            5 => 'livewire.dashboard.wr',
            6 => 'livewire.dashboard.rektor',
            default => 'livewire.dashboard.approver-generic'
        };
        return view($view);
    }
}
