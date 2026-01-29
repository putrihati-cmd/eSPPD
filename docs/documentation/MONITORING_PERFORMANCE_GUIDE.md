# 📊 MONITORING, LOGGING & PERFORMANCE TOOLS GUIDE

**Status:** Implementation Reference  
**Date:** 29 January 2026  
**Last Updated:** 29 January 2026  
**Environment:** Production for 500+ users

---

## Overview

Comprehensive monitoring is critical for maintaining system health and performance. This guide covers monitoring setup for e-SPPD.

---

## Monitoring Stack

### Layer 1: Application Monitoring
- Laravel Telescope (Dev)
- Laravel Horizon (Queue monitoring)
- Custom application metrics

### Layer 2: Infrastructure Monitoring
- Prometheus (Metrics collection)
- Grafana (Visualization)
- Netdata (Real-time system monitoring)

### Layer 3: Log Aggregation
- ELK Stack (Elasticsearch, Logstash, Kibana)
- Or Cloudflare/Datadog for SaaS solution

### Layer 4: Alerting
- Prometheus Alertmanager
- Email/Slack notifications

---

## 1. Laravel Application Monitoring

### Laravel Telescope (Development Only)

```bash
# Install Telescope
composer require --dev laravel/telescope

# Publish assets
php artisan telescope:install

# Run migrations
php artisan migrate
```

### Laravel Horizon (Queue Monitoring)

```bash
# Install Horizon
composer require laravel/horizon

# Publish assets
php artisan horizon:install

# Configure in config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 4,
            'tries' => 3,
        ],
    ],
],
```

### Custom Application Metrics

```php
// app/Services/MetricsService.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    public function recordMetric(string $name, float $value, array $tags = [])
    {
        $key = "metric:{$name}:" . now()->format('Y-m-d H:i:00');
        
        Cache::increment($key);
        Cache::expire($key, 86400);  // Retain for 24 hours
        
        // Send to Prometheus (optional)
        if (config('services.prometheus.enabled')) {
            $this->sendToPrometheus($name, $value, $tags);
        }
    }
    
    public function getMetrics(string $name, int $hours = 24)
    {
        $metrics = [];
        $now = now();
        
        for ($i = 0; $i < $hours; $i++) {
            $time = $now->copy()->subHours($i)->format('Y-m-d H:i:00');
            $key = "metric:{$name}:{$time}";
            $metrics[$time] = Cache::get($key, 0);
        }
        
        return $metrics;
    }
    
    private function sendToPrometheus(string $name, float $value, array $tags)
    {
        $labels = implode(',', array_map(
            fn($k, $v) => "{$k}=\"{$v}\"",
            array_keys($tags),
            $tags
        ));
        
        $metric = "{$name}{{{$labels}}} {$value}\n";
        
        // Push to Prometheus Pushgateway
        $ch = curl_init(config('services.prometheus.pushgateway'));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $metric,
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
```

---

## 2. Infrastructure Monitoring

### Prometheus Setup

```yaml
# docker-compose.monitoring.yml

version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    container_name: esppd_prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./monitoring/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml
      - ./monitoring/prometheus/rules.yml:/etc/prometheus/rules.yml
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
    restart: unless-stopped

  grafana:
    image: grafana/grafana:latest
    container_name: esppd_grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
      - GF_INSTALL_PLUGINS=grafana-piechart-panel
    volumes:
      - grafana_data:/var/lib/grafana
    depends_on:
      - prometheus
    restart: unless-stopped

  node_exporter:
    image: prom/node-exporter:latest
    container_name: esppd_node_exporter
    ports:
      - "9100:9100"
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'
      - '--collector.filesystem.mount-points-exclude=^/(sys|proc|dev|host|etc)($$|/)'
    restart: unless-stopped

volumes:
  prometheus_data:
  grafana_data:
```

### Prometheus Configuration

```yaml
# monitoring/prometheus/prometheus.yml

global:
  scrape_interval: 15s
  evaluation_interval: 15s
  external_labels:
    environment: 'production'
    app: 'esppd'

scrape_configs:
  # Node Exporter (System metrics)
  - job_name: 'node'
    static_configs:
      - targets: ['localhost:9100']
    relabel_configs:
      - source_labels: [__address__]
        target_label: instance
        replacement: 'esppd-server'

  # PHP-FPM Metrics (optional)
  - job_name: 'php-fpm'
    static_configs:
      - targets: ['localhost:9253']

  # PostgreSQL Exporter (optional)
  - job_name: 'postgres'
    static_configs:
      - targets: ['localhost:9187']

alerting:
  alertmanagers:
    - static_configs:
        - targets: ['localhost:9093']

rule_files:
  - 'rules.yml'
```

### Alert Rules

```yaml
# monitoring/prometheus/rules.yml

groups:
  - name: esppd_alerts
    interval: 30s
    rules:
      # High CPU usage
      - alert: HighCpuUsage
        expr: rate(node_cpu_seconds_total[5m]) > 0.8
        for: 5m
        annotations:
          summary: "High CPU usage detected"
          description: "CPU usage is {{ $value | humanizePercentage }} on {{ $labels.instance }}"

      # High Memory usage
      - alert: HighMemoryUsage
        expr: (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes) < 0.2
        for: 5m
        annotations:
          summary: "High memory usage detected"
          description: "Only {{ $value | humanizePercentage }} memory available"

      # High Disk usage
      - alert: HighDiskUsage
        expr: (node_filesystem_avail_bytes / node_filesystem_size_bytes) < 0.1
        for: 5m
        annotations:
          summary: "High disk usage detected"
          description: "Only {{ $value | humanizePercentage }} disk space available"

      # Database down
      - alert: PostgresDown
        expr: pg_up == 0
        for: 1m
        annotations:
          summary: "PostgreSQL is down"
          description: "PostgreSQL on {{ $labels.instance }} is not responding"

      # High query time
      - alert: SlowQueries
        expr: pg_slow_queries > 10
        for: 5m
        annotations:
          summary: "Many slow queries detected"
          description: "{{ $value }} slow queries in last 5 minutes"
```

---

## 3. Application Logging

### Laravel Logging Configuration

```php
// config/logging.php

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,  // Rotate logs every 14 days
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],

    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel',
        'emoji' => ':boom:',
        'level' => 'critical',  // Only log critical errors to Slack
    ],

    'stderr' => [
        'driver' => 'monolog',
        'handler' => StreamHandler::class,
        'with' => [
            'stream' => 'php://stderr',
        ],
    ],
],
```

### Structured Logging

```php
// Usage in application
use Illuminate\Support\Facades\Log;

// Info logging
Log::info('SPPD created', [
    'sppd_id' => $spd->id,
    'employee_id' => $spd->employee_id,
    'user_id' => auth()->id(),
    'timestamp' => now(),
]);

// Error logging with context
Log::error('SPPD approval failed', [
    'sppd_id' => $spd->id,
    'approver_id' => $approver->id,
    'error' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString(),
]);

// Performance logging
Log::info('Query executed', [
    'query' => 'SELECT * FROM spds',
    'duration_ms' => 245,
    'rows' => 50,
]);
```

### Request/Response Logging Middleware

```php
// app/Http/Middleware/LogRequests.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = (microtime(true) - $startTime) * 1000;  // ms
        
        Log::info('HTTP Request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration_ms' => round($duration, 2),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);
        
        return $response;
    }
}
```

### Database Query Logging

```php
// app/Providers/AppServiceProvider.php

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

public function boot()
{
    if ($this->app->isLocal()) {
        DB::listen(function (QueryExecuted $query) {
            if ($query->time > 1000) {  // Log queries > 1 second
                Log::warning('Slow query detected', [
                    'query' => $query->sql,
                    'bindings' => $query->bindings,
                    'duration_ms' => $query->time,
                ]);
            }
        });
    }
}
```

---

## 4. Custom Dashboard

### Real-Time Metrics Endpoint

```php
// app/Http/Controllers/MetricsController.php

namespace App\Http\Controllers;

use App\Services\MetricsService;
use Illuminate\Support\Facades\Cache;

class MetricsController extends Controller
{
    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'uptime' => $this->getUptime(),
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->getQueueStats(),
        ]);
    }
    
    public function dashboard()
    {
        return response()->json([
            'requests_per_minute' => Cache::get('metrics:requests:rpm', 0),
            'error_rate' => Cache::get('metrics:errors:rate', 0),
            'avg_response_time_ms' => Cache::get('metrics:response:avg', 0),
            'active_users' => Cache::get('metrics:users:active', 0),
            'queue_length' => $this->getQueueLength(),
            'cache_hit_rate' => $this->getCacheHitRate(),
        ]);
    }
    
    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => 'healthy'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }
    
    private function checkRedis()
    {
        try {
            \Redis::ping();
            return ['status' => 'healthy'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }
    
    private function getQueueStats()
    {
        $redis = \Redis::connection();
        return [
            'pending' => $redis->llen('queues:default'),
            'failed' => \DB::table('failed_jobs')->count(),
        ];
    }
}
```

---

## 5. Alerting & Notifications

### Alert Configuration

```php
// config/alerts.php

return [
    'channels' => ['mail', 'slack'],
    
    'thresholds' => [
        'cpu_usage' => 80,          // %
        'memory_usage' => 75,       // %
        'disk_usage' => 85,         // %
        'response_time' => 2000,    // ms
        'error_rate' => 5,          // %
        'queue_backlog' => 1000,    // jobs
    ],
    
    'recipients' => [
        'admin' => 'admin@esppd.ac.id',
        'devops' => 'devops@esppd.ac.id',
    ],
];
```

### Alert Notification

```php
// app/Notifications/SystemAlertNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class SystemAlertNotification extends Notification
{
    public function __construct(
        private string $alertType,
        private array $data
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'slack'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("🚨 {$this->alertType} Alert")
            ->line("Alert: {$this->alertType}")
            ->line("Severity: {$this->data['severity']}")
            ->line("Details: {$this->data['message']}")
            ->action('View Dashboard', route('admin.dashboard'));
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->from('e-SPPD Alerts')
            ->content("🚨 {$this->alertType}")
            ->attachmentColor('danger')
            ->attachment(function ($attachment) {
                $attachment
                    ->field('Severity', $this->data['severity'])
                    ->field('Message', $this->data['message'])
                    ->field('Time', now());
            });
    }
}
```

---

## 6. Performance Testing Tools

### Load Testing with Apache Bench

```bash
# Basic load test
ab -n 1000 -c 100 https://esppd.uinsaizu.ac.id/

# With custom headers
ab -n 1000 -c 100 \
   -H "Authorization: Bearer TOKEN" \
   https://esppd.uinsaizu.ac.id/api/spds

# Output analysis
ab -n 1000 -c 100 -g results.tsv https://esppd.uinsaizu.ac.id/
```

### Load Testing with Artillery

```bash
# Install
npm install -g artillery

# Create test file (load-test.yml)
config:
  target: "https://esppd.uinsaizu.ac.id"
  phases:
    - duration: 60
      arrivalRate: 10

scenarios:
  - name: "SPPD List"
    flow:
      - get:
          url: "/api/spds"
          headers:
            Authorization: "Bearer {{ token }}"

# Run test
artillery run load-test.yml
```

---

## 7. Monitoring Checklist

- [ ] Prometheus installed and configured
- [ ] Grafana dashboards created
- [ ] Alert rules configured
- [ ] Slack integration setup
- [ ] Application logging configured
- [ ] Database query monitoring enabled
- [ ] Queue monitoring (Horizon) setup
- [ ] Health check endpoint implemented
- [ ] Performance testing completed
- [ ] Alerting tested

---

**Document Version:** 1.0  
**Last Updated:** 29 January 2026  
**Maintained by:** Infrastructure & Monitoring Team
