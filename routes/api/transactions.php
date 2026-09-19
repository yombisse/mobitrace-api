<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/export', [TransactionController::class, 'export']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::patch('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel']);
});
