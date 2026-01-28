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

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // SPPD CRUD
    Route::get('/sppd', [SppdController::class, 'index']);
    Route::post('/sppd', [SppdController::class, 'store']);
    Route::get('/sppd/{spd}', [SppdController::class, 'show']);
    Route::put('/sppd/{spd}', [SppdController::class, 'update']);
    Route::delete('/sppd/{spd}', [SppdController::class, 'destroy']);

    // SPPD Actions
    Route::post('/sppd/{spd}/submit', [SppdController::class, 'submit']);
    Route::post('/sppd/{spd}/approve', [SppdController::class, 'approve']);
    Route::post('/sppd/{spd}/reject', [SppdController::class, 'reject']);
    Route::post('/sppd/{spd}/complete', [SppdController::class, 'complete']);

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
