<?php

namespace App\Services;

use App\Models\SPD;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardCacheService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'dashboard_';

    /**
     * Get cached dashboard metrics for current user
     */
    public static function getUserMetrics(): array
    {
        $userId = Auth::id();
        $userRole = Auth::user()->roles->first()?->name ?? 'staff';
        $cacheKey = self::CACHE_PREFIX . "user_{$userId}_metrics";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $userRole) {
            $query = SPD::query();

            if ($userRole !== 'admin') {
                $query->where('user_id', $userId);
            }

            return [
                'total' => (clone $query)->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ];
        });
    }

    /**
     * Get cached monthly metrics
     */
    public static function getMonthlyMetrics(): array
    {
        $userId = Auth::id();
        $userRole = Auth::user()->roles->first()?->name ?? 'staff';
        $cacheKey = self::CACHE_PREFIX . "monthly_{$userId}_" . Carbon::now()->format('Y-m');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $userRole) {
            $now = Carbon::now();
            $query = SPD::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month);

            if ($userRole !== 'admin') {
                $query->where('user_id', $userId);
            }

            return [
                'approved_this_month' => (clone $query)->where('status', 'approved')->count(),
                'rejected_this_month' => (clone $query)->where('status', 'rejected')->count(),
                'pending_this_month' => (clone $query)->where('status', 'pending')->count(),
            ];
        });
    }

    /**
     * Get cached trend data for last 6 months
     */
    public static function getTrendData(): array
    {
        $userId = Auth::id();
        $userRole = Auth::user()->roles->first()?->name ?? 'staff';
        $cacheKey = self::CACHE_PREFIX . "trend_{$userId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $userRole) {
            $trendData = [];
            $now = Carbon::now();

            for ($i = 5; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $query = SPD::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month);

                if ($userRole !== 'admin') {
                    $query->where('user_id', $userId);
                }

                $trendData[] = [
                    'month' => $month->translatedFormat('M Y'),
                    'approved' => (clone $query)->where('status', 'approved')->count(),
                    'pending' => (clone $query)->where('status', 'pending')->count(),
                    'rejected' => (clone $query)->where('status', 'rejected')->count(),
                ];
            }

            return $trendData;
        });
    }

    /**
     * Clear all dashboard caches for a user
     */
    public static function clearUserCache(int $userId = null): void
    {
        $userId = $userId ?? Auth::id();

        Cache::forget(self::CACHE_PREFIX . "user_{$userId}_metrics");
        Cache::forget(self::CACHE_PREFIX . "monthly_{$userId}_" . Carbon::now()->format('Y-m'));
        Cache::forget(self::CACHE_PREFIX . "trend_{$userId}");
    }

    /**
     * Clear all dashboard caches
     */
    public static function clearAllCaches(): void
    {
        Cache::flush(); // For production, use more targeted cache clearing
    }
}
