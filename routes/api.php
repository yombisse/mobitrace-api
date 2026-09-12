<?php

use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/reseaux.php';
require __DIR__.'/api/clients.php';
require __DIR__.'/api/transactions.php';
require __DIR__.'/api/history.php';
require __DIR__.'/api/dashboard.php';

Route::get('/health', function () {
    return ApiResponse::success([
        'status' => 'ok',
    ], 'API opérationnelle.');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('/health/authenticated', function () {
        return ApiResponse::success([
            'status' => 'ok',
        ], 'API authentifiée opérationnelle.');
    });
});
