<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'show']);
});
