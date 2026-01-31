<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class ActivityDashboard extends Component
{
    public ?int $selectedUserId = null;
    public string $period = '7days'; // 7days, 30days, 90days, alltime

    #[Computed]
    public function periodDays()
    {
        return match($this->period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'alltime' => 999999,
        };
    }

    #[Computed]
    public function userActivities()
    {
        $query = AuditLog::query()
            ->with('user')
            ->whereDate('created_at', '>=', now()->subDays($this->periodDays));

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    #[Computed]
    public function activityStats()
    {
        $logs = $this->userActivities();

        return [
            'total' => $logs->count(),
            'users' => $logs->pluck('user_id')->unique()->count(),
            'entities' => $logs->pluck('entity')->unique()->count(),
            'actions' => [
                'create' => $logs->where('action', 'create')->count(),
                'update' => $logs->where('action', 'update')->count(),
                'delete' => $logs->where('action', 'delete')->count(),
                'approve' => $logs->where('action', 'approve')->count(),
                'reject' => $logs->where('action', 'reject')->count(),
            ]
        ];
    }

    #[Computed]
    public function topUsers()
    {
        return AuditLog::query()
            ->with('user')
            ->whereDate('created_at', '>=', now()->subDays($this->periodDays))
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as total')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'user' => User::find($item->user_id),
                'count' => $item->total,
            ]);
    }

    #[Computed]
    public function topEntities()
    {
        return AuditLog::query()
            ->whereDate('created_at', '>=', now()->subDays($this->periodDays))
            ->groupBy('entity')
            ->selectRaw('entity, COUNT(*) as total')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.admin.activity-dashboard');
    }
}
