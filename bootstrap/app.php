<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Force password change middleware (for first-time login)
        $middleware->append(\App\Http\Middleware\CheckPasswordReset::class);

        // Security headers middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Metrics & monitoring middleware
        $middleware->append(\App\Http\Middleware\LogRequests::class);

        // Custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'role.level' => \App\Http\Middleware\CheckRoleLevel::class,
            'approval-level' => \App\Http\Middleware\CheckApprovalLevel::class,
            'cache.response' => \App\Http\Middleware\CacheResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
