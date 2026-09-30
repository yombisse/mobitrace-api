<?php

namespace App\Http\Controllers;

use App\Models\AgentReseau;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaisseSoldeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $soldes = AgentReseau::where('user_id', $user->id)
            ->where('actif', true)
            ->with('reseau')
            ->get()
            ->map(function ($agentReseau) {
                return [
                    'reseau' => [
                        'id' => $agentReseau->reseau->id,
                        'nom' => $agentReseau->reseau->nom,
                        'code' => $agentReseau->reseau->code,
                        'logo' => $agentReseau->reseau->logo,
                    ],
                    'solde_float' => $agentReseau->solde_float,
                    'solde_cash' => $agentReseau->solde_cash,
                ];
            });

        return ApiResponse::success($soldes, 'Soldes récupérés avec succès.');
    }
}
