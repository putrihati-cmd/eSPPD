# 💾 CACHING STRATEGY & IMPLEMENTATION GUIDE

**Status:** Implementation Reference  
**Date:** 29 January 2026  
**Last Updated:** 29 January 2026  
**Cache Driver:** Redis (Recommended for Production)

---

## Overview

Caching is critical for handling 500+ users efficiently. This guide outlines the caching strategy for e-SPPD.

---

## Cache Layers

### Layer 1: Query Result Caching (Application Level)
- **TTL:** 5-60 minutes
- **Storage:** Redis
- **Use Case:** Dashboard stats, master data lookups

### Layer 2: HTTP Response Caching (Browser Level)
- **TTL:** 1-24 hours
- **Storage:** Browser cache
- **Use Case:** Static assets, PDF files

### Layer 3: Session Caching (Server Level)
- **TTL:** 120 minutes (user session lifetime)
- **Storage:** Redis
- **Use Case:** User authentication, role info

### Layer 4: ORM Query Caching (Framework Level)
- **TTL:** 1-60 minutes
- **Storage:** Redis
- **Use Case:** Eloquent model queries

---

## Implementation Guide

### Configuration (.env)

```env
# Cache Configuration
CACHE_DRIVER=redis
CACHE_KEY_PREFIX=esppd_
CACHE_REDIS_CONNECTION=default

# Session Configuration
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_LIFETIME=120

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Separate Redis databases
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2
REDIS_SESSION_DB=3
```

### Redis Database Allocation

```
DB 0: Default (not used)
DB 1: Cache (CACHE_REDIS_DB)
DB 2: Queue (QUEUE_REDIS_DB)
DB 3: Session (SESSION_REDIS_DB)
DB 4: Reserved for future use
```

### Cache Configuration (config/cache.php)

```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'lock_connection' => 'default',
],

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'prefix' => 'esppd_cache:',
    ],
    
    'spds_cache' => [
        'driver' => 'redis',
        'connection' => 'default',
        'prefix' => 'esppd_sppd:',
    ],
    
    'dashboard_cache' => [
        'driver' => 'redis',
        'connection' => 'default',
        'prefix' => 'esppd_dash:',
    ],
],
```

---

## Caching Strategies by Feature

### 1. Dashboard Caching

```php
// app/Services/DashboardService.php

class DashboardService
{
    public function getStats($user)
    {
        // Cache key based on user role
        $cacheKey = "dashboard:stats:{$user->role_id}";
        
        // 15 minute cache
        return Cache::remember($cacheKey, 900, function () {
            return [
                'total_sppd' => Spd::count(),
                'pending_approval' => Spd::where('status', 'pending')
                    ->count(),
                'monthly_total' => Spd::whereMonth('created_at', now()->month)
                    ->sum('approved_amount'),
                'budget_used' => $this->calculateBudgetUsed(),
                'overdue_approvals' => $this->getOverdueApprovals(),
            ];
        });
    }
    
    public function getApprovalQueue($user)
    {
        return Cache::remember(
            "approval_queue:{$user->id}",
            300,  // 5 minutes
            function () use ($user) {
                return Approval::where('approver_id', $user->id)
                    ->where('status', 'pending')
                    ->with('sppd.employee')
                    ->orderBy('created_at')
                    ->get();
            }
        );
    }
}
```

### 2. Master Data Caching

```php
// app/Services/MasterDataService.php

class MasterDataService
{
    public function getUnits()
    {
        // Cache for 1 hour
        return Cache::remember('units:all', 3600, function () {
            return Unit::select('id', 'code', 'name', 'organization_id')
                ->active()
                ->orderBy('name')
                ->get();
        });
    }
    
    public function getEmployeesByUnit($unitId)
    {
        return Cache::remember(
            "employees:unit:{$unitId}",
            3600,
            function () use ($unitId) {
                return Employee::where('unit_id', $unitId)
                    ->select('id', 'name', 'nip', 'position')
                    ->active()
                    ->get();
            }
        );
    }
    
    public function getRoles()
    {
        return Cache::remember('roles:all', 86400, function () {
            return Role::with('permissions')
                ->orderBy('level', 'desc')
                ->get();
        });
    }
    
    public function getBudgets($fiscalYear)
    {
        return Cache::remember(
            "budgets:fy:{$fiscalYear}",
            3600,
            function () use ($fiscalYear) {
                return Budget::where('fiscal_year', $fiscalYear)
                    ->with('unit')
                    ->get();
            }
        );
    }
}
```

### 3. SPPD Caching

```php
// app/Services/SpdService.php

class SpdService
{
    public function getSpdDetails($spdId)
    {
        return Cache::remember(
            "spd:detail:{$spdId}",
            600,  // 10 minutes
            function () use ($spdId) {
                return Spd::with([
                    'employee',
                    'unit',
                    'approvals',
                    'approvals.approver',
                    'tripReport'
                ])->find($spdId);
            }
        );
    }
    
    public function getSpdsByUser($userId)
    {
        return Cache::remember(
            "spd:user:{$userId}",
            300,  // 5 minutes
            function () use ($userId) {
                return Spd::where('employee_id', $userId)
                    ->with('approvals')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            }
        );
    }
    
    public function getPendingApprovalsCount()
    {
        return Cache::remember(
            'pending_approvals:count',
            60,  // 1 minute
            function () {
                return Approval::where('status', 'pending')
                    ->count();
            }
        );
    }
}
```

### 4. Budget Tracking Caching

```php
// app/Services/BudgetService.php

class BudgetService
{
    public function getBudgetSummary($unitId, $fiscalYear)
    {
        return Cache::remember(
            "budget:summary:{$unitId}:{$fiscalYear}",
            1800,  // 30 minutes
            function () use ($unitId, $fiscalYear) {
                $budget = Budget::where('unit_id', $unitId)
                    ->where('fiscal_year', $fiscalYear)
                    ->first();
                
                if (!$budget) {
                    return null;
                }
                
                return [
                    'allocated' => $budget->allocated_amount,
                    'spent' => $this->calculateSpent($unitId, $fiscalYear),
                    'remaining' => $budget->allocated_amount - 
                        $this->calculateSpent($unitId, $fiscalYear),
                    'percentage_used' => ($this->calculateSpent($unitId, $fiscalYear) / 
                        $budget->allocated_amount) * 100,
                ];
            }
        );
    }
    
    private function calculateSpent($unitId, $fiscalYear)
    {
        return Spd::where('unit_id', $unitId)
            ->whereYear('created_at', $fiscalYear)
            ->where('status', 'approved')
            ->sum('approved_amount');
    }
}
```

### 5. Report Caching

```php
// app/Services/ReportService.php

class ReportService
{
    public function getMonthlyReport($unitId, $month, $year)
    {
        return Cache::remember(
            "report:monthly:{$unitId}:{$year}:{$month}",
            86400,  // 24 hours
            function () use ($unitId, $month, $year) {
                return Spd::where('unit_id', $unitId)
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->with(['employee', 'approvals'])
                    ->get();
            }
        );
    }
    
    public function getAnnualSummary($year)
    {
        return Cache::remember(
            "report:annual:{$year}",
            86400,
            function () use ($year) {
                return Spd::whereYear('created_at', $year)
                    ->selectRaw('unit_id, COUNT(*) as count, SUM(approved_amount) as total')
                    ->groupBy('unit_id')
                    ->with('unit')
                    ->get();
            }
        );
    }
}
```

---

## Cache Invalidation

### Automatic Invalidation (Events)

```php
// app/Models/Spd.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Spd extends Model
{
    protected static function booted()
    {
        // Invalidate caches when SPPD changes
        static::updated(function ($spd) {
            // Clear SPPD caches
            Cache::forget("spd:detail:{$spd->id}");
            Cache::forget("spd:user:{$spd->employee_id}");
            
            // Clear dashboard cache
            Cache::tags('dashboard')->flush();
            
            // Clear budget caches
            Cache::forget("budget:summary:{$spd->unit_id}:*");
        });
        
        static::deleted(function ($spd) {
            // Clear caches on deletion
            Cache::forget("spd:detail:{$spd->id}");
            Cache::forget("spd:user:{$spd->employee_id}");
            Cache::tags('dashboard')->flush();
        });
    }
}
```

### Manual Invalidation (In Services)

```php
// app/Services/ApprovalService.php

class ApprovalService
{
    public function approveSpd($sppd, $approver)
    {
        // Process approval
        $approval = Approval::create([...]);
        
        // Invalidate related caches
        Cache::forget("spd:detail:{$sppd->id}");
        Cache::forget("approval_queue:{$approver->id}");
        Cache::forget("pending_approvals:count");
        Cache::forget("dashboard:stats:{$approver->role_id}");
        
        return $approval;
    }
}
```

### Tag-Based Invalidation

```php
// Store data with tags
Cache::tags(['dashboard', 'stats'])->put('key', $value, 900);
Cache::tags(['budget', 'unit:1'])->put('key', $value, 3600);

// Flush by tag
Cache::tags('dashboard')->flush();  // Flush all dashboard cache
Cache::tags('budget')->flush();     // Flush all budget cache
Cache::tags(['unit:1'])->flush();   // Flush unit 1 data
```

---

## Cache Warming (Pre-Loading)

### On Application Startup

```php
// app/Console/Commands/WarmCache.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MasterDataService;
use Illuminate\Support\Facades\Cache;

class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Warm up cache with master data';
    
    public function handle(MasterDataService $service)
    {
        $this->info('Warming up cache...');
        
        // Cache all master data
        $service->getUnits();
        $service->getRoles();
        $service->getBudgets(now()->year);
        
        $this->info('Cache warmed successfully');
    }
}
```

### Schedule Cache Warming

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Warm cache every hour
    $schedule->command('cache:warm')
        ->hourly()
        ->onSuccess(function () {
            Log::info('Cache warming completed');
        });
    
    // Refresh dashboard cache every 15 minutes
    $schedule->call(function () {
        Cache::tags('dashboard')->flush();
    })->everyFifteenMinutes();
}
```

---

## Monitoring Cache Performance

### Cache Hit Rate Monitoring

```php
// app/Services/CacheMonitor.php

class CacheMonitor
{
    public function logCacheAccess($key, $hit = true)
    {
        $metric = $hit ? 'cache_hit' : 'cache_miss';
        
        StatsD::increment($metric);
        
        Log::debug("Cache {$metric}", ['key' => $key]);
    }
    
    public function getCacheStats()
    {
        // Redis INFO stats
        $redis = Redis::connection();
        $info = $redis->info('stats');
        
        return [
            'hits' => $info['keyspace_hits'],
            'misses' => $info['keyspace_misses'],
            'hit_rate' => $info['keyspace_hits'] / 
                ($info['keyspace_hits'] + $info['keyspace_misses']) * 100,
        ];
    }
}
```

### Redis Monitoring Commands

```bash
# Monitor Redis in real-time
redis-cli MONITOR

# Get Redis statistics
redis-cli INFO stats

# Get memory usage
redis-cli INFO memory

# Get cache keys
redis-cli KEYS "esppd_*"

# Get cache size
redis-cli DBSIZE

# Check Redis status
redis-cli PING  # Should return PONG
```

---

## Best Practices

### ✅ DO:
1. Use meaningful cache keys (include version/context)
2. Set appropriate TTLs based on data volatility
3. Implement cache invalidation strategy
4. Monitor cache hit rates
5. Use cache tags for grouped invalidation
6. Warm critical caches on startup
7. Log cache operations in development

### ❌ DON'T:
1. Cache everything (only high-cost operations)
2. Use very long TTLs (stale data issues)
3. Cache user-sensitive data without encryption
4. Forget to invalidate related caches
5. Store large objects in cache (memory issue)
6. Use complex cache keys (hard to manage)

---

## Cache Key Naming Convention

```
Prefix:Feature:Context:ID:Version

Examples:
- spd:detail:123
- dashboard:stats:2  (role_id = 2)
- budget:summary:1:2025  (unit_id:fiscal_year)
- approval_queue:5  (user_id)
- report:monthly:1:1:2025  (unit_id:month:year)
```

---

## Performance Impact

### Expected Improvements with Caching

| Feature | Without Cache | With Cache | Improvement |
|---------|---------------|-----------|-------------|
| Dashboard Load | 2000ms | 200ms | 10x faster |
| SPPD List | 1500ms | 100ms | 15x faster |
| Budget Summary | 1000ms | 50ms | 20x faster |
| Approval Queue | 800ms | 30ms | 26x faster |

---

## Troubleshooting

### Issue: Cache not updating
**Solution:** 
- Check cache invalidation logic
- Verify cache keys are correct
- Check TTL hasn't expired

### Issue: Stale data in cache
**Solution:**
- Implement proper cache invalidation
- Use shorter TTLs
- Add cache versioning

### Issue: Redis memory full
**Solution:**
- Monitor Redis memory usage
- Reduce cache TTL
- Implement cache eviction policy

```redis
# Set eviction policy in redis.conf
maxmemory-policy allkeys-lru  # Evict least recently used
```

---

## Verification Checklist

- [ ] Redis installed and running
- [ ] CACHE_DRIVER=redis in .env
- [ ] REDIS_CACHE_DB configured
- [ ] All caching code reviewed
- [ ] Cache invalidation implemented
- [ ] Cache warming configured
- [ ] Monitoring setup
- [ ] Performance testing completed

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Maintained by:** Performance Engineering Team
