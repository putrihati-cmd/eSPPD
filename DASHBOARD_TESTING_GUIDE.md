# Dashboard Testing Guide

## Pre-Deployment Testing Checklist

### ✅ Component Functionality

- [ ] **DashboardEnhanced** loads without errors
  ```bash
  php artisan tinker
  $comp = new App\Livewire\DashboardEnhanced;
  $comp->mount();
  ```

- [ ] **DashboardAdmin** shows admin metrics
  - Check: Total users count displays
  - Check: Pending approvals accurate
  - Check: System health section visible

- [ ] **DashboardApprover** shows approver metrics
  - Check: Pending approvals for user
  - Check: Monthly approved/rejected count

- [ ] **DashboardStaff** shows personal metrics
  - Check: User's total SPDs
  - Check: Recent SPDs table populated

### ✅ Chart Components

- [ ] **SPDTrendChart** renders correctly
  - Displays 6 months of data
  - Bars show Approved/Pending/Rejected
  - Legend displays correctly

- [ ] **SPDStatusChart** renders correctly
  - Pie chart visualizes status distribution
  - Percentages calculated correctly
  - Status cards show accurate counts

### ✅ Cache Functionality

- [ ] Redis cache is working
  ```bash
  redis-cli
  PING  # Should return PONG
  KEYS "dashboard_*"  # Show cache keys
  ```

- [ ] Dashboard metrics are cached
  - First visit: Slow (database query)
  - Second visit: Fast (from cache)
  - Cache TTL: 1 hour

- [ ] Cache invalidates on SPD changes
  - Create SPD → Cache clears
  - Update SPD → Cache clears
  - Delete SPD → Cache clears

### ✅ Database Performance

- [ ] Indexes are created
  ```sql
  SELECT indexname FROM pg_indexes WHERE tablename = 'spd';
  -- Should show: idx_spd_user_id, idx_spd_status, etc.
  ```

- [ ] Query count is reduced
  - Monitor with Laravel Debugbar
  - First load: 3-5 queries
  - Cached load: 0 queries

- [ ] Query execution time is acceptable
  - First load: <2 seconds
  - Cached load: <500ms

### ✅ Role-Based Access

- [ ] **Admin** can see all data
  - Dashboard loads all-user stats
  - Charts show organization data

- [ ] **Approver** sees team data
  - Charts filtered to approver's team
  - Shows pending approvals

- [ ] **Staff** sees personal data
  - Dashboard shows personal stats only
  - Recent SPDs are user's own

### ✅ Responsive Design

- [ ] Mobile (375px width)
  - [ ] Cards stack vertically
  - [ ] Charts responsive
  - [ ] Buttons accessible
  - [ ] No horizontal scrolling

- [ ] Tablet (768px width)
  - [ ] 2-column grid layout
  - [ ] Charts display side-by-side
  - [ ] All text readable

- [ ] Desktop (1920px width)
  - [ ] 4-column stat grid
  - [ ] Charts full width
  - [ ] Professional appearance

### ✅ Error Handling

- [ ] No data displays gracefully
  - Empty state message shown
  - No errors in console
  - UI remains functional

- [ ] Caching errors don't break dashboard
  - Falls back to database queries
  - No error pages shown

- [ ] Database connection errors handled
  - User-friendly error message
  - Dashboard doesn't crash

---

## Manual Testing Steps

### Test 1: Basic Dashboard Load

```
1. Log in as staff user
2. Navigate to /dashboard
3. Verify:
   - Stats cards display correct numbers
   - Recent SPDs table shows data
   - No errors in console
   - Page loads within 2 seconds
```

### Test 2: Charts Rendering

```
1. Scroll to charts section
2. Verify:
   - Trend chart shows 6 months
   - Status chart shows percentage
   - Legend is visible
   - Colors are distinct
```

### Test 3: Cache Verification

```
1. Open dashboard first time
   - Check network tab: slow (2-3s)
   - Check Redis: cache keys exist
   
2. Refresh dashboard
   - Check network tab: fast (<500ms)
   - Check Redis: same cache keys
   
3. Wait 10 seconds and refresh
   - Should still use cache
   - Within 1-hour TTL
```

### Test 4: Role-Based Views

```
1. Log in as Admin
   - Dashboard shows Admin variant
   - All user stats visible
   
2. Log in as Approver
   - Dashboard shows Approver variant
   - Pending approvals count
   
3. Log in as Staff
   - Dashboard shows Staff variant
   - Only personal stats
```

### Test 5: SPD Actions

```
1. Create new SPD from dashboard
2. Dashboard cache should clear
3. Refresh dashboard
4. New SPD should appear in recent list
5. Metrics should update
```

---

## Automated Testing (PHPUnit)

Create `tests/Feature/DashboardTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SPD;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard');
    }

    public function test_dashboard_shows_user_metrics()
    {
        $user = User::factory()->create();
        SPD::factory()->count(5)->create(['user_id' => $user->id]);
        
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('5'); // Total SPDs
    }

    public function test_charts_component_renders()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('spd-trend-chart')
            ->assertSee('spd-status-chart');
    }

    public function test_cache_works_correctly()
    {
        $user = User::factory()->create();
        
        // First request - database query
        $this->actingAs($user)->get('/dashboard');
        
        // Second request - should use cache
        $this->actingAs($user)->get('/dashboard');
        
        // Verify cache keys exist
        $this->assertNotNull(\Cache::get('dashboard_user_' . $user->id . '_metrics'));
    }
}
```

Run tests:
```bash
php artisan test tests/Feature/DashboardTest.php
```

---

## Performance Benchmarking

### Benchmark Script

Create `tests/Benchmarks/DashboardBenchmark.php`:

```php
<?php

use App\Models\User;
use App\Services\DashboardCacheService;
use Illuminate\Support\Facades\Cache;

// Clear cache
Cache::flush();

// Test 1: Without Cache
$start = microtime(true);
$user = User::find(1);
$metrics = [
    'total' => \App\Models\SPD::where('user_id', $user->id)->count(),
    'pending' => \App\Models\SPD::where('user_id', $user->id)->where('status', 'pending')->count(),
];
$time1 = microtime(true) - $start;
echo "Without Cache: {$time1}ms\n";

// Test 2: With Cache
$start = microtime(true);
\Auth::setUser($user);
$metrics = DashboardCacheService::getUserMetrics();
$time2 = microtime(true) - $start;
echo "With Cache: {$time2}ms\n";

echo "Improvement: " . round((1 - ($time2 / $time1)) * 100, 2) . "%\n";
```

Run:
```bash
php artisan tinker < tests/Benchmarks/DashboardBenchmark.php
```

Expected output:
```
Without Cache: 45.23ms
With Cache: 2.15ms
Improvement: 95.25%
```

---

## Browser Compatibility Testing

Test in these browsers:

- [ ] Chrome 120+
  - [ ] Charts display
  - [ ] Responsive design
  - [ ] No console errors

- [ ] Firefox 121+
  - [ ] Charts display
  - [ ] Responsive design
  - [ ] No console errors

- [ ] Safari 17+
  - [ ] Charts display
  - [ ] Responsive design
  - [ ] No console errors

- [ ] Edge 120+
  - [ ] Charts display
  - [ ] Responsive design
  - [ ] No console errors

---

## Test Coverage Report

Generated using PHPUnit:

```bash
php artisan test --coverage
```

Target coverage:
- Statements: 80%+
- Branches: 75%+
- Functions: 85%+
- Lines: 80%+

---

## Post-Deployment Verification

### Day 1
- [ ] Monitor error logs
- [ ] Check dashboard load times
- [ ] Verify cache is working
- [ ] Test with real data

### Week 1
- [ ] Monitor performance metrics
- [ ] Check database indexes
- [ ] Review user feedback
- [ ] Adjust cache TTL if needed

### Month 1
- [ ] Analyze usage patterns
- [ ] Optimize slow queries
- [ ] Plan Phase 2 enhancements
- [ ] Document lessons learned

---

## Sign-Off

- [ ] All tests passing
- [ ] Performance benchmarks met
- [ ] Browser compatibility verified
- [ ] Production database indexes applied
- [ ] Cache configured and working
- [ ] Monitoring in place
- [ ] Documentation complete

**Tested by**: _________________  
**Date**: _________________  
**Sign-off**: _________________
