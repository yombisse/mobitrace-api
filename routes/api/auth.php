<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:forgot-password');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/me', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
});
