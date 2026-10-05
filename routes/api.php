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

    Route::get('/mes-reseaux', [App\Http\Controllers\MesReseauxController::class, 'index']);
    Route::patch('/mes-reseaux/{reseau}', [App\Http\Controllers\MesReseauxController::class, 'update']);
    Route::get('/caisse/solde', [App\Http\Controllers\CaisseSoldeController::class, 'index']);
    Route::get('/caisse/sessions/aujourdhui', [App\Http\Controllers\CaisseSessionController::class, 'aujourdhui']);
    Route::get('/caisse/sessions/historique', [App\Http\Controllers\CaisseSessionController::class, 'historique']);
    Route::get('/caisse/sessions/{session}/bilan', [App\Http\Controllers\CaisseSessionController::class, 'bilan']);
    Route::get('/caisse/sessions/{session}/export-pdf', [App\Http\Controllers\CaisseSessionController::class, 'exportPdf']);
    Route::post('/caisse/sessions/ouvrir', [App\Http\Controllers\CaisseSessionController::class, 'ouvrir']);
    Route::post('/caisse/sessions/{session}/cloturer', [App\Http\Controllers\CaisseSessionController::class, 'cloturer']);
    Route::post('/caisse/ajustements', [App\Http\Controllers\CaisseAjustementController::class, 'store']);
});
