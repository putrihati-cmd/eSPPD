# 🗄️ DATABASE OPTIMIZATION GUIDE

**Status:** Implementation Reference  
**Date:** 29 January 2026  
**Last Updated:** 29 January 2026  
**Framework:** Laravel 12 with PostgreSQL

---

## Overview

This guide provides database optimization strategies for the e-SPPD system to handle 500+ concurrent users with optimal performance.

---

## Index Strategy

### Critical Indexes to Add

#### 1. SPPD Table Indexes
```sql
-- Primary performance indexes
CREATE INDEX idx_sppd_employee_id ON spds(employee_id);
CREATE INDEX idx_sppd_status ON spds(status);
CREATE INDEX idx_sppd_created_at ON spds(created_at);
CREATE INDEX idx_sppd_organization_id ON spds(organization_id);

-- Approval workflow indexes
CREATE INDEX idx_sppd_unit_id ON spds(unit_id);
CREATE INDEX idx_sppd_created_date_status ON spds(created_at, status);

-- Composite indexes for common queries
CREATE INDEX idx_sppd_employee_status ON spds(employee_id, status);
CREATE INDEX idx_sppd_org_status_date ON spds(organization_id, status, created_at DESC);
```

#### 2. Approval Table Indexes
```sql
CREATE INDEX idx_approval_sppd_id ON approvals(sppd_id);
CREATE INDEX idx_approval_approver_id ON approvals(approver_id);
CREATE INDEX idx_approval_status ON approvals(status);
CREATE INDEX idx_approval_sequence ON approvals(sppd_id, approval_sequence);
CREATE INDEX idx_approval_created_at ON approvals(created_at);

-- Approval queue optimization
CREATE INDEX idx_approval_pending ON approvals(status, created_at) 
WHERE status = 'pending';
```

#### 3. Employee Table Indexes
```sql
CREATE INDEX idx_employee_nip ON employees(nip) UNIQUE;
CREATE INDEX idx_employee_user_id ON employees(user_id);
CREATE INDEX idx_employee_unit_id ON employees(unit_id);
CREATE INDEX idx_employee_organization_id ON employees(organization_id);
```

#### 4. Budget Table Indexes
```sql
CREATE INDEX idx_budget_unit_id ON budgets(unit_id);
CREATE INDEX idx_budget_fiscal_year ON budgets(fiscal_year);
CREATE INDEX idx_budget_unit_year ON budgets(unit_id, fiscal_year);
CREATE INDEX idx_budget_status ON budgets(status);
```

#### 5. Trip Report (Laporan Perjalanan) Indexes
```sql
CREATE INDEX idx_trip_report_sppd_id ON trip_reports(sppd_id);
CREATE INDEX idx_trip_report_employee_id ON trip_reports(employee_id);
CREATE INDEX idx_trip_report_created_at ON trip_reports(created_at);
CREATE INDEX idx_trip_report_status ON trip_reports(status);
```

#### 6. Audit Log Indexes (if using)
```sql
CREATE INDEX idx_audit_log_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_log_model ON audit_logs(model_type, model_id);
CREATE INDEX idx_audit_log_created_at ON audit_logs(created_at);
CREATE INDEX idx_audit_log_action ON audit_logs(action);
```

### Performance Verification

```sql
-- Check if indexes are being used
EXPLAIN ANALYZE SELECT * FROM spds 
WHERE employee_id = 1 AND status = 'approved';

-- Expected: "Index Scan using idx_sppd_employee_status"

-- Verify all indexes created
SELECT indexname, indexdef FROM pg_indexes 
WHERE tablename IN ('spds', 'approvals', 'employees', 'budgets');

-- Find missing indexes on foreign keys
SELECT constraint_name, table_name, column_name 
FROM information_schema.key_column_usage 
WHERE constraint_type = 'FOREIGN KEY' 
  AND table_schema = 'public';
```

---

## Query Optimization

### N+1 Query Prevention

#### ❌ AVOID (N+1 Problem):
```php
// Bad: Creates N+1 queries
$spds = Spd::all();  // 1 query
foreach ($spds as $spd) {
    echo $spd->employee->name;  // N queries
}
```

#### ✅ RECOMMENDED (Eager Loading):
```php
// Good: Creates 2 queries only
$spds = Spd::with('employee')->get();
foreach ($spds as $spd) {
    echo $spd->employee->name;  // No additional queries
}

// Better: Multiple relations
$spds = Spd::with(['employee', 'unit', 'approvals'])
    ->get();

// Best: Nested relations
$spds = Spd::with([
    'employee.user',
    'employee.unit',
    'approvals.approver',
    'approvals.approver.unit'
])->get();
```

### Explicit Index Usage

```php
// Force index usage in Laravel queries
$spds = Spd::where('employee_id', $employee_id)
    ->where('status', 'approved')
    ->orderBy('created_at', 'desc')
    ->get();

// Generated SQL will use idx_sppd_employee_status index
```

### Pagination Optimization

```php
// Bad: Full table scan for large datasets
$spds = Spd::paginate(15);

// Good: Use where clause to reduce dataset
$spds = Spd::where('status', '!=', 'archived')
    ->paginate(15);

// Better: Use cursor pagination for large datasets
$spds = Spd::orderBy('id')->cursorPaginate(15);
```

---

## Caching Strategy

### Query Result Caching

```php
// Cache expensive queries
$stats = Cache::remember('dashboard-stats', 900, function () {
    return [
        'total_sppd' => Spd::count(),
        'pending_approval' => Spd::where('status', 'pending')->count(),
        'total_spent' => Spd::sum('approved_amount'),
    ];
});

// Cache table data for lookups
$units = Cache::remember('units-list', 3600, function () {
    return Unit::select('id', 'name', 'code')->get();
});
```

### Cache Invalidation Strategy

```php
// Invalidate cache when data changes
public function update(Request $request, Spd $spd)
{
    $spd->update($request->validated());
    
    // Invalidate relevant caches
    Cache::forget('dashboard-stats');
    Cache::forget('spd-' . $spd->id);
    Cache::tags('sppd')->flush();
    
    return response()->json($spd);
}
```

### Database Query Cache

```php
// Cache database queries at application level
use Illuminate\Support\Facades\Cache;

$approvers = Cache::rememberForever('approvers-hierarchy', function () {
    return User::with('role')
        ->where('is_active', true)
        ->get();
});

// When roles update, flush:
Cache::forget('approvers-hierarchy');
```

---

## Performance Tuning

### PostgreSQL Configuration

```ini
# postgresql.conf tuning for 500 users

# Memory
shared_buffers = 256MB          # 25% of RAM
effective_cache_size = 1GB      # 50-75% of RAM
work_mem = 4MB                  # RAM / (max_connections * 2)

# Connections
max_connections = 200           # For 500 users, allow 200 connections
max_prepared_transactions = 100 

# Query Execution
random_page_cost = 1.1          # For SSD
effective_io_concurrency = 200

# Logging (for slow query analysis)
log_min_duration_statement = 1000  # Log queries > 1 second
log_statement = 'mod'              # Log DML only
log_connections = on
log_disconnections = on
```

### Application-Level Optimization

#### Connection Pooling
```php
// config/database.php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST'),
    'port' => env('DB_PORT'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
    'sslmode' => 'prefer',
    
    // Connection pooling
    'options' => [
        'connect_timeout' => 10,
        'application_name' => 'esppd_app',
    ]
],

// Alternatively, use PgBouncer for connection pooling
// config/pgbouncer.ini
// [esppd_db]
// host = localhost
// port = 5432
// dbname = esppd_db
```

#### Lazy Loading Prevention
```php
// In AppServiceProvider.php
use Illuminate\Database\Eloquent\Builder;

public function boot()
{
    // Warn about lazy loading in development
    if ($this->app->isLocal()) {
        Builder::preventLazyLoading();
    }
    
    // Prevent lazy loading in production
    // Model::preventAccessingMissingAttributes();
}
```

---

## Monitoring & Analysis

### Query Performance Analysis

```bash
# Enable query timing
php artisan tinker

# Test query performance
$startTime = microtime(true);
$spds = Spd::where('status', 'approved')->get();
$endTime = microtime(true);
echo "Query took: " . ($endTime - $startTime) . " seconds";

# Use Laravel Debugbar in development
composer require barryvdh/laravel-debugbar --dev
```

### Slow Query Log

```bash
# Enable slow query logging in PostgreSQL
sudo -u postgres psql -d esppd_db -c 
"ALTER SYSTEM SET log_min_duration_statement = 1000;"

# Reload config
sudo systemctl reload postgresql

# Check slow queries
sudo tail -f /var/log/postgresql/postgresql.log | grep "duration:"
```

### Performance Metrics

```php
// app/Services/PerformanceMonitor.php
class PerformanceMonitor
{
    public function logQueryTime(string $query, float $duration)
    {
        if ($duration > 1.0) {  // Log queries > 1 second
            Log::warning("Slow query detected", [
                'query' => $query,
                'duration_ms' => $duration * 1000
            ]);
        }
    }
}
```

---

## Migration for Adding Indexes

```php
// database/migrations/2025_01_29_000001_add_performance_indexes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SPPD indexes
        Schema::table('spds', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('organization_id');
            $table->index('unit_id');
            $table->index(['created_at', 'status']);
            $table->index(['employee_id', 'status']);
            $table->index(['organization_id', 'status', 'created_at']);
        });

        // Approval indexes
        Schema::table('approvals', function (Blueprint $table) {
            $table->index('sppd_id');
            $table->index('approver_id');
            $table->index('status');
            $table->index(['sppd_id', 'approval_sequence']);
            $table->index('created_at');
        });

        // Employee indexes
        Schema::table('employees', function (Blueprint $table) {
            $table->unique('nip');
            $table->index('user_id');
            $table->index('unit_id');
            $table->index('organization_id');
        });

        // Budget indexes
        Schema::table('budgets', function (Blueprint $table) {
            $table->index('unit_id');
            $table->index('fiscal_year');
            $table->index(['unit_id', 'fiscal_year']);
            $table->index('status');
        });

        // Trip Report indexes
        Schema::table('trip_reports', function (Blueprint $table) {
            $table->index('sppd_id');
            $table->index('employee_id');
            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        // Rollback indexes
        Schema::table('spds', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['status']);
            // ... etc
        });
    }
};
```

### Running the Migration

```bash
# Create migration
php artisan make:migration add_performance_indexes

# Run migration
php artisan migrate

# Verify indexes
php artisan tinker
DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'spds';")
```

---

## Load Testing

### Using Apache Bench

```bash
# Test homepage load
ab -n 1000 -c 100 https://esppd.uinsaizu.ac.id/

# Test API endpoint
ab -n 1000 -c 100 -H "Authorization: Bearer TOKEN" \
   https://esppd.uinsaizu.ac.id/api/spds

# Results interpret
# Requests per second: >= 100 RPS is good
# Time per request: < 100ms is good
# Failed requests: should be 0
```

### Using Laravel Horizon (for queue monitoring)

```bash
# Install Horizon
composer require laravel/horizon

# Publish assets
php artisan horizon:install

# Monitor queue performance
# Navigate to: /horizon
```

---

## Scaling Recommendations

### For 500+ Users

1. **Database Optimization (Done)**
   - ✅ Add indexes on foreign keys
   - ✅ Add indexes on frequently queried columns
   - ✅ Use eager loading to prevent N+1 queries

2. **Caching Layer**
   - ✅ Use Redis for session/cache
   - ✅ Cache dashboard statistics (15-min TTL)
   - ✅ Cache master data (1-hour TTL)

3. **Queue Processing**
   - ✅ Use Redis as queue driver
   - ✅ Configure queue workers with supervisor
   - ✅ Monitor queue backlog

4. **Server Resources**
   - CPU: 4+ cores recommended
   - RAM: 8GB+ recommended
   - Storage: SSD for database

5. **Application Optimization**
   - Use connection pooling
   - Enable query caching
   - Compress response payloads
   - Use CDN for static assets

---

## Verification Checklist

- [ ] All recommended indexes created
- [ ] Eager loading implemented in controllers
- [ ] Pagination added to list endpoints
- [ ] Query result caching configured
- [ ] Slow query log enabled
- [ ] Connection pooling configured
- [ ] Load testing completed
- [ ] Performance metrics reviewed

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Maintained by:** Database Administration Team
