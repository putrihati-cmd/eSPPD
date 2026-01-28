<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $ttl = 60): Response
    {
        // Skip caching for authenticated users if needed, or cache based on user ID
        // For 500+ users, we might want to cache public pages or specific API endpoints
        
        $key = 'route_' . $request->url() . '_' . ($request->user() ? $request->user()->id : 'guest');

        return Cache::remember($key, $ttl, function () use ($request, $next) {
            return $next($request);
        });
    }
}
