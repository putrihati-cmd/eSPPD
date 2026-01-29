<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SppdController;
use App\Http\Controllers\Api\MobileApiController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check routes (public, for monitoring)
Route::get('/health', [\App\Http\Controllers\HealthCheckController::class, 'health']);
Route::get('/health/metrics', [\App\Http\Controllers\HealthCheckController::class, 'metrics']);

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // SPPD CRUD (with both /sppd and /spd aliases for compatibility)
    Route::get('/sppd', [SppdController::class, 'index']);
    Route::post('/sppd', [SppdController::class, 'store']);
    Route::get('/sppd/{spd}', [SppdController::class, 'show']);
    Route::put('/sppd/{spd}', [SppdController::class, 'update']);
    Route::delete('/sppd/{spd}', [SppdController::class, 'destroy']);

    // SPPD Alias routes (/spd for test compatibility)
    Route::get('/spd', [SppdController::class, 'index']);
    Route::post('/spd', [SppdController::class, 'store']);
    Route::get('/spd/{spd}', [SppdController::class, 'show']);
    Route::put('/spd/{spd}', [SppdController::class, 'update']);
    Route::delete('/spd/{spd}', [SppdController::class, 'destroy']);

    // SPPD Actions
    Route::post('/sppd/{spd}/submit', [SppdController::class, 'submit']);
    Route::post('/sppd/{spd}/approve', [SppdController::class, 'approve']);
    Route::post('/sppd/{spd}/reject', [SppdController::class, 'reject']);
    Route::post('/sppd/{spd}/complete', [SppdController::class, 'complete']);

    // SPPD Actions (/spd alias routes)
    Route::post('/spd/{spd}/submit', [SppdController::class, 'submit']);
    Route::post('/spd/{spd}/approve', [SppdController::class, 'approve']);
    Route::post('/spd/{spd}/reject', [SppdController::class, 'reject']);
    Route::post('/spd/{spd}/complete', [SppdController::class, 'complete']);

    // Approval endpoints (for workflow tests)
    Route::get('/spd/{spd}/approvals', [SppdController::class, 'listApprovals']);
    Route::post('/spd/{spd}/approvals', [SppdController::class, 'storeApproval']);
    Route::get('/sppd/{spd}/approvals', [SppdController::class, 'listApprovals']);
    Route::post('/sppd/{spd}/approvals', [SppdController::class, 'storeApproval']);

    // PDF Export
    Route::post('/spd/{spd}/export-pdf', [SppdController::class, 'exportPdf']);
    Route::post('/sppd/{spd}/export-pdf', [SppdController::class, 'exportPdf']);

    // Mobile API
    Route::prefix('mobile')->group(function () {
        Route::get('/dashboard', [MobileApiController::class, 'dashboard']);
        Route::get('/sppd', [MobileApiController::class, 'listSppd']);
        Route::get('/sppd/{spd}', [MobileApiController::class, 'showSppd']);
        Route::post('/sppd/{spd}/submit', [MobileApiController::class, 'quickSubmit']);
        Route::post('/sppd/{spd}/approve', [MobileApiController::class, 'quickApprove']);
        Route::get('/notifications', [MobileApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [MobileApiController::class, 'markNotificationRead']);
    });

    // Webhooks
    Route::prefix('webhooks')->group(function () {
        Route::get('/', [WebhookController::class, 'index']);
        Route::post('/', [WebhookController::class, 'store']);
        Route::put('/{id}', [WebhookController::class, 'update']);
        Route::delete('/{id}', [WebhookController::class, 'destroy']);
        Route::post('/{id}/test', [WebhookController::class, 'test']);
    });
});
