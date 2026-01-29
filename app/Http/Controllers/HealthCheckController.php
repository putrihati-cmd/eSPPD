<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * Health check and metrics endpoint for monitoring systems
 */
class HealthCheckController extends Controller
{
    /**
     * Basic health check
     */
    public function health(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => []
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['database'] = 'failed';
            $health['status'] = 'unhealthy';
        }

        // Cache check
        try {
            Cache::put('health_check', 1, now()->addMinutes(1));
            $health['checks']['cache'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['cache'] = 'failed';
            $health['status'] = 'unhealthy';
        }

        // Redis check
        try {
            Redis::ping();
            $health['checks']['redis'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['redis'] = 'failed';
        }

        // Queue check
        $health['checks']['queue_workers'] = $this->getQueueWorkerStatus();

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;
        return response()->json($health, $statusCode);
    }

    /**
     * Detailed metrics for Prometheus
     */
    public function metrics(): Response
    {
        $metrics = [];

        // Request metrics
        $metrics[] = "# HELP requests_total Total number of HTTP requests";
        $metrics[] = "# TYPE requests_total counter";
        $metrics[] = 'requests_total ' . (Cache::get('metrics:requests:total') ?? 0);

        // Get method breakdown
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $count = Cache::get("metrics:requests:$method") ?? 0;
            $metrics[] = "requests_total{method=\"$method\"} $count";
        }

        // Error metrics
        $metrics[] = "\n# HELP errors_total Total number of errors";
        $metrics[] = "# TYPE errors_total counter";
        $metrics[] = 'errors_total ' . (Cache::get('metrics:errors:total') ?? 0);

        // Database metrics
        $metrics[] = "\n# HELP database_connections Current database connections";
        $metrics[] = "# TYPE database_connections gauge";
        try {
            $connections = DB::select("SELECT count(*) as cnt FROM pg_stat_activity")[0]->cnt ?? 0;
            $metrics[] = "database_connections $connections";
        } catch (\Exception $e) {
            $metrics[] = "database_connections 0";
        }

        // Cache metrics
        $metrics[] = "\n# HELP cache_operations_total Total cache operations";
        $metrics[] = "# TYPE cache_operations_total counter";
        $metrics[] = 'cache_operations_total ' . (Cache::get('metrics:cache:operations') ?? 0);

        // Queue metrics
        $metrics[] = "\n# HELP queue_jobs_pending Pending jobs in queue";
        $metrics[] = "# TYPE queue_jobs_pending gauge";
        $metrics[] = 'queue_jobs_pending ' . $this->getPendingJobsCount();

        // Memory metrics
        $metrics[] = "\n# HELP php_memory_usage Current memory usage in bytes";
        $metrics[] = "# TYPE php_memory_usage gauge";
        $metrics[] = 'php_memory_usage ' . memory_get_usage();

        $metricsText = implode("\n", $metrics);
        return response($metricsText, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Check queue worker status from supervisor
     */
    private function getQueueWorkerStatus(): array
    {
        $status = [
            'default' => 0,
            'notifications' => 0,
            'imports' => 0,
            'reports' => 0,
        ];

        try {
            exec('supervisorctl status 2>&1', $output);
            foreach ($output as $line) {
                foreach (array_keys($status) as $queue) {
                    if (strpos($line, $queue) !== false && strpos($line, 'RUNNING') !== false) {
                        preg_match('/(\d+) RUNNING/', $line, $matches);
                        if (isset($matches[1])) {
                            $status[$queue] = (int)$matches[1];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Supervisor not available, return zero status
        }

        return $status;
    }

    /**
     * Get pending jobs count
     */
    private function getPendingJobsCount(): int
    {
        try {
            $queues = ['default', 'notifications', 'imports', 'reports'];
            $total = 0;
            foreach ($queues as $queue) {
                $total += Redis::llen("queues:$queue");
            }
            return $total;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
