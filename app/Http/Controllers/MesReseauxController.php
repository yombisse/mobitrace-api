<?php

namespace App\Http\Controllers;

use App\Http\Requests\Caisse\ToggleReseauRequest;
use App\Models\AgentReseau;
use App\Models\CaisseSession;
use App\Models\Reseau;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MesReseauxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $reseaux = Reseau::leftJoin('agent_reseaux', function ($join) use ($user) {
            $join->on('reseaux.id', '=', 'agent_reseaux.reseau_id')
                ->where('agent_reseaux.user_id', $user->id);
        })
            ->select([
                'reseaux.id',
                'reseaux.nom',
                'reseaux.code',
                'reseaux.logo',
                'agent_reseaux.actif',
                'agent_reseaux.solde_float',
                'agent_reseaux.solde_cash',
            ])
            ->orderBy('reseaux.nom')
            ->get()
            ->map(function ($reseau) use ($user) {
                $soldeFloatSuggere = null;
                $soldeCashSuggere = null;

                // Chercher la dernière session clôturée pour suggérer ses soldes réels
                if ($reseau->actif ?? false) {
                    $derniereSession = CaisseSession::whereHas('agentReseau', function ($query) use ($user, $reseau) {
                        $query->where('user_id', $user->id)->where('reseau_id', $reseau->id);
                    })
                    ->where('statut', 'cloturee')
                    ->orderBy('cloturee_at', 'desc')
                    ->first();

                    if ($derniereSession !== null && $derniereSession->solde_float_reel !== null) {
                        $soldeFloatSuggere = $derniereSession->solde_float_reel;
                        $soldeCashSuggere = $derniereSession->solde_cash_reel;
                    }
                }

                return [
                    'id' => $reseau->id,
                    'nom' => $reseau->nom,
                    'code' => $reseau->code,
                    'logo' => $reseau->logo,
                    'actif' => $reseau->actif ?? false,
                    'solde_float' => $reseau->solde_float ?? 0,
                    'solde_cash' => $reseau->solde_cash ?? 0,
                    'solde_float_suggere' => $soldeFloatSuggere,
                    'solde_cash_suggere' => $soldeCashSuggere,
                ];
            });

        return ApiResponse::success($reseaux, 'Réseaux récupérés avec succès.');
    }

    public function update(ToggleReseauRequest $request, string $reseauId): JsonResponse
    {
        $user = $request->user();

        $agentReseau = AgentReseau::firstOrCreate(
            ['user_id' => $user->id, 'reseau_id' => $reseauId],
            ['actif' => true, 'solde_float' => 0, 'solde_cash' => 0]
        );

        $agentReseau->update(['actif' => $request->validated('actif')]);

        return ApiResponse::success([
            'id' => $agentReseau->id,
            'actif' => $agentReseau->actif,
        ], 'Statut du réseau mis à jour avec succès.');
    }
}
