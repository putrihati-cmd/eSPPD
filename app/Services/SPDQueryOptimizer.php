<?php

namespace App\Services;

use App\Models\SPD;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SPDQueryOptimizer
{
    /**
     * Get optimized query for user's SPDs with eager loading
     */
    public static function getUserSpdsOptimized(): Builder
    {
        $userId = Auth::id();
        $userRole = Auth::user()->roles->first()?->name ?? 'staff';

        $query = SPD::with(['user', 'approver', 'status']) // Eager load relationships
            ->select('id', 'user_id', 'destination', 'start_date', 'end_date', 'status', 'created_at', 'budget');

        if ($userRole !== 'admin') {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    /**
     * Get recent SPDs with pagination
     */
    public static function getRecentSpds(int $limit = 5)
    {
        return self::getUserSpdsOptimized()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get SPDs by status with counts
     */
    public static function getSpdsByStatus(): array
    {
        $query = self::getUserSpdsOptimized();

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
        ];
    }

    /**
     * Get total budget for user's SPDs (approved only)
     */
    public static function getTotalBudget(): float
    {
        return self::getUserSpdsOptimized()
            ->where('status', 'approved')
            ->sum('budget') ?? 0;
    }

    /**
     * Get pending approvals (for approvers/admins)
     */
    public static function getPendingApprovals(): int
    {
        $userRole = Auth::user()->roles->first()?->name ?? 'staff';

        if ($userRole === 'admin') {
            return SPD::where('status', 'pending')->count();
        } elseif ($userRole === 'approver') {
            $userId = Auth::id();
            return SPD::where('approver_id', $userId)
                ->where('status', 'pending')
                ->count();
        }

        return 0;
    }
}
