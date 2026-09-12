<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/lookup', [ClientController::class, 'lookup']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::get('/clients/{client}/transactions', [ClientController::class, 'transactions']);
    Route::patch('/clients/{client}', [ClientController::class, 'update']);
});
