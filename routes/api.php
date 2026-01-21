<?php

use Illuminate\Support\Facades\Route;
use Plugins\TrustpilotReview\Controllers\TrustpilotController;
use Plugins\TrustpilotReview\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for Trustpilot plugin
|
*/

// User routes (requires authentication)
Route::middleware(['auth:api'])->group(function () {
    Route::get('/api/trustpilot/check/{serverId}', [TrustpilotController::class, 'checkPopup']);
    Route::post('/api/trustpilot/dismiss/{serverId}', [TrustpilotController::class, 'dismissPopup']);
});

// Admin routes (requires admin authentication)
Route::middleware(['auth:api', 'admin'])->prefix('api/admin/trustpilot')->group(function () {
    Route::get('/settings', [AdminController::class, 'getSettings']);
    Route::post('/settings', [AdminController::class, 'updateSettings']);
    Route::get('/stats', [AdminController::class, 'getStats']);
});
