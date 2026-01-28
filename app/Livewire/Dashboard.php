<?php

namespace App\Livewire;

use App\Models\Budget;
use App\Models\Spd;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;

        // Statistics
        $totalSpdThisMonth = Spd::where('organization_id', $organizationId)
            ->thisMonth()
            ->count();

        $pendingApproval = Spd::where('organization_id', $organizationId)
            ->status('submitted')
            ->count();

        $approvedSpd = Spd::where('organization_id', $organizationId)
            ->status('approved')
            ->count();

        $completedSpd = Spd::where('organization_id', $organizationId)
            ->status('completed')
            ->count();

        // Recent SPDs
        $recentSpds = Spd::where('organization_id', $organizationId)
            ->with(['employee', 'budget'])
            ->latest()
            ->take(5)
            ->get();

        // Budget summary
        $budgets = Budget::where('organization_id', $organizationId)
            ->where('year', now()->year)
            ->where('is_active', true)
            ->get();

        $totalBudget = $budgets->sum('total_budget');
        $usedBudget = $budgets->sum('used_budget');
        $availableBudget = $totalBudget - $usedBudget;

        return view('livewire.dashboard', [
            'totalSpdThisMonth' => $totalSpdThisMonth,
            'pendingApproval' => $pendingApproval,
            'approvedSpd' => $approvedSpd,
            'completedSpd' => $completedSpd,
            'recentSpds' => $recentSpds,
            'totalBudget' => $totalBudget,
            'usedBudget' => $usedBudget,
            'availableBudget' => $availableBudget,
        ])->layout('layouts.app', ['header' => 'Dashboard']);
    }
}
