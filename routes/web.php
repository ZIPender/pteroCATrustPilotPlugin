<?php

use Illuminate\Support\Facades\Route;
use Plugins\TrustpilotReview\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Web routes for Trustpilot plugin admin panel
|
*/

// Admin routes (requires admin authentication)
Route::middleware(['auth', 'admin'])->prefix('admin/trustpilot')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('trustpilot.admin');
});
