<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\Budget;
use App\Models\Employee;

class CacheService
{
    /**
     * Cache TTL in minutes
     */
    protected const TTL_MASTER = 1440; // 24 hours
    protected const TTL_STATS = 15; // 15 minutes
    protected const TTL_USER = 60; // 1 hour

    /**
     * Get Cached Master Data (Units, Organizations)
     */
    public function getUnits(string $organizationId)
    {
        return Cache::remember("units_{$organizationId}", self::TTL_MASTER, function () use ($organizationId) {
            return Unit::where('organization_id', $organizationId)->get();
        });
    }

    public function getBudgets(string $organizationId, int $year)
    {
        return Cache::remember("budgets_{$organizationId}_{$year}", self::TTL_MASTER, function () use ($organizationId, $year) {
            return Budget::where('organization_id', $organizationId)
                ->where('year', $year)
                ->get();
        });
    }

    public function getEmployees(string $organizationId)
    {
        return Cache::remember("employees_{$organizationId}", self::TTL_MASTER, function () use ($organizationId) {
            return Employee::where('organization_id', $organizationId)->get();
        });
    }

    /**
     * Cache Dashboard Statistics
     */
    public function getDashboardStats(string $organizationId)
    {
        return Cache::remember("dashboard_stats_{$organizationId}", self::TTL_STATS, function () use ($organizationId) {
            // Logic would be moved here from Dashboard controller if fully refactoring
            // For now, this is a placeholder or can be used for specific heavy stats
            return [];
        });
    }

    /**
     * Clear Cache for Organization
     */
    public function clearOrganizationCache(string $organizationId)
    {
        Cache::forget("units_{$organizationId}");
        Cache::forget("employees_{$organizationId}");
        Cache::forget("dashboard_stats_{$organizationId}");
        Cache::forget("budgets_{$organizationId}_" . now()->year);
    }

    /**
     * Generate cache key with prefix
     */
    public function makeKey(string $key, ?int $id = null): string
    {
        if ($id !== null) {
            return "{$key}:{$id}";
        }
        return "app_{$key}";
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return Cache::has($this->makeKey($key));
    }

    /**
     * Invalidate specific cache key
     */
    public function invalidate(string $key): bool
    {
        return Cache::forget($this->makeKey($key));
    }

    /**
     * Get value with expiration
     */
    public function getWithExpiration(string $key, int $ttl = 60): mixed
    {
        return Cache::remember($this->makeKey($key), $ttl, function () {
            return null;
        });
    }
}
