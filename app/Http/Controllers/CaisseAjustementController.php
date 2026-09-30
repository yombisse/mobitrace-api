<?php

namespace App\Http\Controllers;

use App\Http\Requests\Caisse\AjustementCaisseRequest;
use App\Models\AgentReseau;
use App\Models\User;
use App\Services\CaisseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaisseAjustementController extends Controller
{
    public function __construct(
        private readonly CaisseService $caisseService
    ) {}

    public function store(AjustementCaisseRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $agentReseau = $this->caisseService->getOrCreateAgentReseau($user, $data['reseau_id']);

        $ajustement = $this->caisseService->ajusterCaisse(
            $agentReseau,
            $data['type'],
            $data['montant_float'],
            $data['montant_cash'],
            $data['motif']
        );

        return ApiResponse::success([
            'id' => $ajustement->id,
            'type' => $ajustement->type,
            'montant_float' => $ajustement->montant_float,
            'montant_cash' => $ajustement->montant_cash,
            'motif' => $ajustement->motif,
            'created_at' => $ajustement->created_at,
        ], 'Ajustement de caisse effectué avec succès.', 201);
    }
}
