# 🚀 QUEUE OPTIMIZATION & JOB PROCESSING GUIDE

**Status:** Implementation Reference  
**Date:** 29 January 2026  
**Last Updated:** 29 January 2026  
**Target:** Handle 500+ concurrent users

---

## Overview

Queue processing is critical for handling heavy operations asynchronously. This guide covers queue optimization for the e-SPPD system.

---

## Architecture

```
┌─────────────────┐
│  User Action    │
│ (Upload, Email) │
└────────┬────────┘
         │
         │ Dispatch Job
         ↓
┌──────────────────────┐
│   Redis Queue DB 2   │
│  (Pending Jobs)      │
└────────┬─────────────┘
         │
         │ Worker Process
         ↓
┌──────────────────────┐
│  Process Job         │
│  (Heavy Operation)   │
└────────┬─────────────┘
         │
         ├─ Success
         │   ↓
         │ Broadcast Event
         │
         └─ Failure
             ↓
         Retry Queue
```

---

## Queue Configuration

### .env Configuration

```env
# Queue Configuration
QUEUE_CONNECTION=redis
QUEUE_REDIS_DB=2

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Failed Queue
QUEUE_FAILED_TABLE=failed_jobs
```

### config/queue.php

```php
'default' => env('QUEUE_CONNECTION', 'redis'),

'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('QUEUE_NAME', 'default'),
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
    
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],
],
```

---

## Job Classes

### 1. Document Generation Job

```php
// app/Jobs/GenerateSpdPdfJob.php

namespace App\Jobs;

use App\Models\Spd;
use App\Services\DocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSpdPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;              // Retry 3 times
    public $timeout = 300;          // 5 minutes timeout
    public $maxExceptions = 3;
    public $backoff = [10, 60, 300]; // Backoff timing
    
    public function __construct(
        private Spd $spd
    ) {
        $this->onQueue('default');
        $this->delay(now()->addSeconds(5));
    }
    
    public function handle(DocumentService $service)
    {
        try {
            $pdf = $service->generateSpdPdf($this->spd);
            
            // Store file
            $this->spd->update([
                'pdf_path' => $pdf['path'],
                'pdf_generated_at' => now(),
            ]);
            
            // Broadcast success event
            SpdPdfGenerated::dispatch($this->spd);
            
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
    
    public function failed(\Throwable $exception)
    {
        \Log::error("PDF Generation failed for SPPD {$this->spd->id}", [
            'error' => $exception->getMessage(),
        ]);
        
        // Notify admin
        FailedJobNotification::dispatch($this->spd, $exception);
    }
}
```

### 2. Excel Import Job

```php
// app/Jobs/BulkImportSpdJob.php

namespace App\Jobs;

use App\Models\Spd;
use App\Imports\SpdImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class BulkImportSpdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 2;
    public $timeout = 600;  // 10 minutes for large files
    public $maxExceptions = 1;
    public $backoff = [30, 300];
    
    public function __construct(
        private string $filePath,
        private int $userId
    ) {
        $this->onQueue('imports');
        $this->delay(now()->addSeconds(10));
    }
    
    public function handle()
    {
        try {
            $import = new SpdImport($this->userId);
            Excel::import($import, $this->filePath);
            
            // Dispatch notification
            ImportCompleted::dispatch(
                $this->userId,
                $import->getRowCount(),
                $import->getFailureCount()
            );
            
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
    
    public function failed(\Throwable $exception)
    {
        \Log::error("Import failed for user {$this->userId}", [
            'file' => $this->filePath,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### 3. Email Notification Job

```php
// app/Jobs/SendApprovalNotificationJob.php

namespace App\Jobs;

use App\Models\Approval;
use App\Notifications\ApprovalPendingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendApprovalNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 5;
    public $timeout = 120;
    public $backoff = [10, 30, 60, 300, 900];
    
    public function __construct(
        private Approval $approval
    ) {
        $this->onQueue('notifications');
        $this->delay(now()->addSeconds(5));
    }
    
    public function handle()
    {
        $approver = $this->approval->approver;
        
        Notification::send($approver, 
            new ApprovalPendingNotification($this->approval)
        );
    }
    
    public function failed(\Throwable $exception)
    {
        \Log::warning("Notification send failed", [
            'approval_id' => $this->approval->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### 4. Report Generation Job

```php
// app/Jobs/GenerateMonthlyReportJob.php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMonthlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $timeout = 600;  // 10 minutes
    
    public function __construct(
        private int $unitId,
        private int $month,
        private int $year
    ) {
        $this->onQueue('reports');
    }
    
    public function handle(ReportService $service)
    {
        try {
            $report = $service->generateMonthlyReport(
                $this->unitId,
                $this->month,
                $this->year
            );
            
            // Store in database
            Report::create([
                'unit_id' => $this->unitId,
                'period' => "{$this->year}-{$this->month}",
                'content' => json_encode($report),
                'generated_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}
```

### 5. Batch Processing Job

```php
// app/Jobs/BulkApprovalJob.php

namespace App\Jobs;

use App\Models\Spd;
use App\Models\Approval;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkApprovalJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 2;
    public $timeout = 300;
    
    public function __construct(
        private int $spdId,
        private int $approverId,
        private string $status
    ) {
        $this->onQueue('default');
    }
    
    public function handle()
    {
        $spd = Spd::find($this->spdId);
        
        $approval = $spd->approvals()
            ->where('approver_id', $this->approverId)
            ->first();
        
        if ($approval) {
            $approval->update([
                'status' => $this->status,
                'decided_at' => now(),
            ]);
        }
    }
}
```

---

## Dispatching Jobs

### From Controllers

```php
// app/Http/Controllers/SpdController.php

namespace App\Http\Controllers;

use App\Jobs\GenerateSpdPdfJob;
use App\Jobs\BulkImportSpdJob;

class SpdController extends Controller
{
    public function store(Request $request)
    {
        $spd = Spd::create($request->validated());
        
        // Dispatch job immediately
        GenerateSpdPdfJob::dispatch($spd);
        
        // Or dispatch with delay
        GenerateSpdPdfJob::dispatch($spd)
            ->delay(now()->addMinutes(5));
        
        return response()->json($spd);
    }
    
    public function import(Request $request)
    {
        $file = $request->file('excel');
        $filePath = $file->store('imports', 'local');
        
        BulkImportSpdJob::dispatch($filePath, auth()->id());
        
        return response()->json([
            'message' => 'Import started. You will be notified when complete.'
        ]);
    }
}
```

### Using Batch Processing

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

// Process multiple items as batch
$spdIds = [1, 2, 3, 4, 5];
$jobs = [];

foreach ($spdIds as $id) {
    $jobs[] = new BulkApprovalJob(
        $id,
        auth()->id(),
        'approved'
    );
}

Bus::batch($jobs)
    ->dispatch()
    ->then(function (Batch $batch) {
        // All jobs completed
        Log::info("Batch approved {$batch->processedJobs()} items");
    })
    ->catch(function (Batch $batch, Throwable $e) {
        // Handle failure
        Log::error("Batch processing failed", ['error' => $e->getMessage()]);
    });
```

---

## Queue Worker Configuration

### Supervisor Configuration

```ini
# /etc/supervisor/conf.d/esppd-queue.conf

[program:esppd-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/esppd/artisan queue:work redis \
    --queue=default,notifications,imports,reports \
    --tries=3 \
    --timeout=300 \
    --max-jobs=1000 \
    --max-time=3600
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/esppd/storage/logs/queue.log
```

### Queue Worker Commands

```bash
# Start queue worker
php artisan queue:work redis

# Start with multiple queues (priority order)
php artisan queue:work redis \
    --queue=default,notifications,imports,reports

# Start with retry settings
php artisan queue:work redis \
    --tries=3 \
    --timeout=300 \
    --backoff=10

# Start with job limit
php artisan queue:work redis \
    --max-jobs=1000 \
    --max-time=3600

# Monitor queue
php artisan queue:monitor

# Restart queue
php artisan queue:restart
```

---

## Queue Monitoring

### Check Queue Status

```bash
# List pending jobs
redis-cli -n 2 KEYS "*queue*"

# Count pending jobs
redis-cli -n 2 LLEN "queues:default"

# View job details
redis-cli -n 2 LRANGE "queues:default" 0 -1

# Monitor queue in real-time
watch 'redis-cli -n 2 LLEN "queues:default"'
```

### Laravel Horizon (Recommended)

```bash
# Install Horizon
composer require laravel/horizon

# Publish assets
php artisan horizon:install

# Start Horizon
php artisan horizon

# Access dashboard: http://localhost/horizon
```

### Database Failed Jobs

```php
// Check failed jobs
php artisan queue:failed

// Retry failed job
php artisan queue:retry 1

// Forget failed job
php artisan queue:forget 1

// Flush all failed jobs
php artisan queue:flush
```

---

## Performance Tuning

### Job Optimization

```php
// Use payload compression for large jobs
class BigJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(private array $largeData)
    {
        // Only serialize essential data
        $this->largeData = array_only(
            $largeData,
            ['id', 'name', 'status']
        );
    }
}

// Use database for job payload (for very large data)
class HugeJob implements ShouldQueue
{
    public function __construct(private int $dataId)
    {
        // Store ID only, fetch data in handle()
    }
    
    public function handle()
    {
        $data = LargeData::find($this->dataId);
        // Process...
    }
}
```

### Queue Configuration Tuning

```php
// config/queue.php

'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => 'default',
    'retry_after' => 90,
    'block_for' => 5,  // Block if queue empty
    'after_commit' => true,  // Dispatch after DB commit
],
```

### Batch Processing for Better Performance

```php
// Process 100 items per batch
Bus::batch(
    collect(range(1, 10000))
        ->map(fn($id) => new ProcessItemJob($id))
        ->chunk(100)
        ->map(fn($chunk) => Bus::batch($chunk)->dispatch())
);
```

---

## Error Handling & Retry Strategy

### Exponential Backoff

```php
class ReliableJob implements ShouldQueue
{
    public $tries = 5;
    
    public function backoff()
    {
        return [
            10,      // 1st retry: 10 seconds
            60,      // 2nd retry: 1 minute
            300,     // 3rd retry: 5 minutes
            900,     // 4th retry: 15 minutes
            3600,    // 5th retry: 1 hour
        ];
    }
}
```

### Max Exceptions

```php
class RobustJob implements ShouldQueue
{
    public $tries = 10;
    public $maxExceptions = 3;  // Fail after 3 unique exceptions
    
    public function handle()
    {
        // Job logic
    }
}
```

### Custom Error Handling

```php
class CustomJob implements ShouldQueue
{
    public function failed(Throwable $exception)
    {
        // Alert admin
        Notification::route('mail', 'admin@example.com')
            ->notify(new JobFailedNotification($this, $exception));
        
        // Log error
        Log::error("Job failed: {$exception->getMessage()}");
        
        // Update database
        FailedJob::create([
            'job' => static::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

---

## Best Practices

### ✅ DO:
1. Use queues for all heavy operations
2. Set appropriate retry counts and timeouts
3. Monitor queue depth and failure rate
4. Implement exponential backoff
5. Log job execution
6. Use meaningful queue names
7. Test job failure scenarios

### ❌ DON'T:
1. Process large files synchronously
2. Query large datasets in jobs without limits
3. Send emails synchronously
4. Store huge payloads in jobs
5. Forget to set job timeout
6. Ignore failed job notifications

---

## Queue Optimization Checklist

- [ ] Queue driver set to Redis
- [ ] Multiple queues configured (default, notifications, imports)
- [ ] Supervisor configured for queue workers
- [ ] Job classes created for heavy operations
- [ ] Retry logic implemented
- [ ] Error handling implemented
- [ ] Monitoring setup (Horizon or custom)
- [ ] Failed job notifications configured
- [ ] Backoff strategy configured
- [ ] Performance testing completed

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Maintained by:** Backend Engineering Team
