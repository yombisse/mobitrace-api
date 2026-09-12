<?php

use App\Http\Controllers\TransactionHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('/transactions/{transaction}/history', [TransactionHistoryController::class, 'index']);
});
