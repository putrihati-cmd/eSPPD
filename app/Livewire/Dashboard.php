<?php

namespace App\Livewire;

use App\Models\Approval;
use App\Models\Budget;
use App\Models\Spd;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;

        // Efficient: Get all status counts in one query
        $statusCounts = Spd::where('organization_id', $organizationId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalSpdThisMonth = Spd::where('organization_id', $organizationId)
            ->thisMonth()
            ->count();
            
        // Map counts (default to 0 if not present)
        $pendingApproval = $statusCounts['submitted'] ?? 0;
        $approvedSpd = $statusCounts['approved'] ?? 0;
        $completedSpd = $statusCounts['completed'] ?? 0;
        
        // Status distribution for pie chart
        $statusDistribution = [
            ['status' => 'Draft', 'count' => $statusCounts['draft'] ?? 0],
            ['status' => 'Pending', 'count' => $pendingApproval],
            ['status' => 'Approved', 'count' => $approvedSpd],
            ['status' => 'Completed', 'count' => $completedSpd],
            ['status' => 'Rejected', 'count' => $statusCounts['rejected'] ?? 0],
        ];

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

        // Monthly trend data (last 6 months) - OPTIMIZED
        $endDate = now()->endOfMonth();
        $startDate = now()->subMonths(5)->startOfMonth();
        
        $trends = Spd::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count, MIN(created_at) as sort_date")
            ->groupBy('month')
            ->orderBy('sort_date')
            ->get();
            
        // Fill in missing months
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthLabel = now()->subMonths($i)->format('M');
            $trend = $trends->firstWhere('month', $monthLabel);
            $monthlyTrend->push([
                'month' => $monthLabel,
                'count' => $trend ? $trend->count : 0,
            ]);
        }

        // Alerts
        $alerts = collect();
        
        // Budget alert (if >80% used)
        if ($totalBudget > 0 && ($usedBudget / $totalBudget) > 0.8) {
            $alerts->push([
                'type' => 'warning',
                'message' => 'Anggaran telah terpakai lebih dari 80%',
            ]);
        }

        // Late approvals (pending > 48 hours)
        $lateApprovals = Approval::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(48))
            ->count();
        if ($lateApprovals > 0) {
            $alerts->push([
                'type' => 'danger',
                'message' => "$lateApprovals approval tertunda lebih dari 48 jam",
            ]);
        }

        // Reports due
        $reportsDue = Spd::where('organization_id', $organizationId)
            ->where('status', 'approved')
            ->where('return_date', '<', now())
            ->whereDoesntHave('report')
            ->count();
        if ($reportsDue > 0) {
            $alerts->push([
                'type' => 'info',
                'message' => "$reportsDue laporan perjalanan belum dibuat",
            ]);
        }

        $view = match ($user->role) {
            'admin' => 'livewire.dashboard.admin',
            'approver' => 'livewire.dashboard.approver',
            'employee' => 'livewire.dashboard.employee',
            default => 'livewire.dashboard.employee', // Default fallback
        };

        return view($view, [
            'totalSpdThisMonth' => $totalSpdThisMonth,
            'pendingApproval' => $pendingApproval,
            'approvedSpd' => $approvedSpd,
            'completedSpd' => $completedSpd,
            'recentSpds' => $recentSpds,
            'totalBudget' => $totalBudget,
            'usedBudget' => $usedBudget,
            'availableBudget' => $availableBudget,
            'monthlyTrend' => $monthlyTrend,
            'statusDistribution' => $statusDistribution,
            'alerts' => $alerts,
        ])->layout('layouts.app', ['header' => 'Dashboard']);
    }
}

