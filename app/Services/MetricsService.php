<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Application metrics collection service for Prometheus
 */
class MetricsService
{
    /**
     * Record request metric
     */
    public static function recordRequest(string $method, string $path, int $statusCode, float $duration): void
    {
        // Increment total requests
        Cache::increment('metrics:requests:total');
        Cache::increment("metrics:requests:$method");

        // Track by status code
        Cache::increment("metrics:requests:status:$statusCode");

        // Track response time
        $bucket = self::getTimeBucket($duration);
        Cache::increment("metrics:requests:duration:$bucket");

        // Log slow requests
        if ($duration > 1000) {
            Log::warning('slow_request', [
                'method' => $method,
                'path' => $path,
                'duration_ms' => $duration,
            ]);
        }
    }

    /**
     * Record database query metric
     */
    public static function recordDatabaseQuery(string $query, float $duration): void
    {
        Cache::increment('metrics:database:queries:total');
        Cache::increment('metrics:database:queries:duration:' . self::getTimeBucket($duration));

        if ($duration > 500) {
            Log::warning('slow_query', [
                'query' => substr($query, 0, 100),
                'duration_ms' => $duration,
            ]);
        }
    }

    /**
     * Record cache operation
     */
    public static function recordCacheOperation(string $operation, string $key, bool $hit = true): void
    {
        Cache::increment("metrics:cache:$operation");
        if ($hit) {
            Cache::increment('metrics:cache:hits');
        } else {
            Cache::increment('metrics:cache:misses');
        }
    }

    /**
     * Record queue job
     */
    public static function recordQueueJob(string $queue, string $job, float $duration, bool $success = true): void
    {
        Cache::increment("metrics:queue:$queue:total");
        Cache::increment("metrics:queue:$queue:duration:" . self::getTimeBucket($duration));

        if ($success) {
            Cache::increment("metrics:queue:$queue:success");
        } else {
            Cache::increment("metrics:queue:$queue:failed");
        }
    }

    /**
     * Record business metric (SPPD created, approved, etc)
     */
    public static function recordBusinessEvent(string $event, string $category = 'general'): void
    {
        Cache::increment("metrics:business:$category:$event");
        Cache::increment('metrics:business:total');
    }

    /**
     * Get current metrics snapshot
     */
    public static function getSnapshot(): array
    {
        return [
            'requests' => [
                'total' => Cache::get('metrics:requests:total', 0),
                'by_method' => [
                    'GET' => Cache::get('metrics:requests:GET', 0),
                    'POST' => Cache::get('metrics:requests:POST', 0),
                    'PUT' => Cache::get('metrics:requests:PUT', 0),
                    'DELETE' => Cache::get('metrics:requests:DELETE', 0),
                    'PATCH' => Cache::get('metrics:requests:PATCH', 0),
                ],
                'errors' => Cache::get('metrics:requests:status:500', 0) +
                           Cache::get('metrics:requests:status:502', 0) +
                           Cache::get('metrics:requests:status:503', 0),
            ],
            'cache' => [
                'hits' => Cache::get('metrics:cache:hits', 0),
                'misses' => Cache::get('metrics:cache:misses', 0),
                'operations' => Cache::get('metrics:cache:operations', 0),
            ],
            'database' => [
                'queries' => Cache::get('metrics:database:queries:total', 0),
            ],
            'queue' => [
                'total' => Cache::get('metrics:queue:total', 0),
                'by_queue' => [
                    'default' => Cache::get('metrics:queue:default:total', 0),
                    'notifications' => Cache::get('metrics:queue:notifications:total', 0),
                    'imports' => Cache::get('metrics:queue:imports:total', 0),
                    'reports' => Cache::get('metrics:queue:reports:total', 0),
                ],
            ],
            'business' => [
                'total_events' => Cache::get('metrics:business:total', 0),
            ],
        ];
    }

    /**
     * Reset metrics
     */
    public static function reset(): void
    {
        Cache::forget('metrics:requests:total');
        Cache::forget('metrics:requests:GET');
        Cache::forget('metrics:requests:POST');
        Cache::forget('metrics:requests:PUT');
        Cache::forget('metrics:requests:DELETE');
        Cache::forget('metrics:requests:PATCH');
        Cache::forget('metrics:cache:hits');
        Cache::forget('metrics:cache:misses');
        Cache::forget('metrics:database:queries:total');
        Cache::forget('metrics:queue:total');
        Cache::forget('metrics:business:total');

        Log::info('metrics_reset');
    }

    /**
     * Get time bucket for aggregation
     */
    private static function getTimeBucket(float $duration): string
    {
        if ($duration < 100) return '0_100ms';
        if ($duration < 500) return '100_500ms';
        if ($duration < 1000) return '500_1000ms';
        if ($duration < 5000) return '1_5s';
        return '5plus';
    }
}
