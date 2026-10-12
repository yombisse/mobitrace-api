<?php

use App\Http\Controllers\ReseauController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('/reseaux', [ReseauController::class, 'index']);
    Route::post('/reseaux', [ReseauController::class, 'store'])->middleware('can:manage-reseaux');
    Route::patch('/reseaux/{reseau}', [ReseauController::class, 'update'])->middleware('can:manage-reseaux');
    Route::delete('/reseaux/{reseau}', [ReseauController::class, 'destroy'])->middleware('can:manage-reseaux');
});
