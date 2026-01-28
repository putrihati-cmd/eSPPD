<?php

namespace App\Livewire\Budgets;

use App\Models\Budget;
use Livewire\Component;

class BudgetIndex extends Component
{
    public function render()
    {
        $user = auth()->user();
        
        $budgets = Budget::where('organization_id', $user->organization_id)
            ->where('year', now()->year)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $totalBudget = $budgets->sum('total_budget');
        $usedBudget = $budgets->sum('used_budget');

        return view('livewire.budgets.budget-index', [
            'budgets' => $budgets,
            'totalBudget' => $totalBudget,
            'usedBudget' => $usedBudget,
        ])->layout('layouts.app', ['header' => 'Budget']);
    }
}
