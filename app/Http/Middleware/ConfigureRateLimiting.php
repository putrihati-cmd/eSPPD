<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate limiting for API endpoints
 * Prevents abuse and DDoS attacks
 */
class ConfigureRateLimiting
{
    public function handle(Request $request, Closure $next)
    {
        $this->configureRateLimiters();
        return $next($request);
    }

    protected function configureRateLimiters(): void
    {
        // API rate limiting: 60 requests per minute per user
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login rate limiting: 3 attempts per 15 minutes per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinutes(15, 3)->by($request->ip());
        });

        // Password reset: 5 attempts per hour per user
        RateLimiter::for('password_reset', function (Request $request) {
            return Limit::perHour(5)->by($request->email ?: $request->ip());
        });

        // File upload: 10 per hour per user
        RateLimiter::for('file_upload', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });

        // Export operations: 5 per hour per user
        RateLimiter::for('export', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
