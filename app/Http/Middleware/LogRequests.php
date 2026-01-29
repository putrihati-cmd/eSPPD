<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Log HTTP requests and response times for monitoring
 */
class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;  // Convert to milliseconds
        $memoryUsed = memory_get_usage() - $startMemory;

        // Log request details
        Log::info('HTTP Request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->status(),
            'duration_ms' => round($duration, 2),
            'memory_kb' => round($memoryUsed / 1024, 2),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Track response times for metrics
        if ($duration > 1000) {  // Log slow requests (> 1 second)
            Log::warning('Slow Request Detected', [
                'method' => $request->method(),
                'path' => $request->path(),
                'duration_ms' => round($duration, 2),
            ]);
        }

        // Increment request counter
        Cache::increment('metrics:requests:total');
        Cache::increment('metrics:requests:' . $request->method());

        // Track error rate
        if ($response->status() >= 400) {
            Cache::increment('metrics:errors:total');
        }

        // Add timing header
        $response->header('X-Response-Time-Ms', round($duration, 2));

        return $response;
    }
}
