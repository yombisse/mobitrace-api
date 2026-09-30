<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\Caisse\CloturerSessionRequest;
use App\Http\Requests\Caisse\OuvrirSessionRequest;
use App\Models\AgentReseau;
use App\Models\CaisseSession;
use App\Models\User;
use App\Services\CaisseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaisseSessionController extends Controller
{
    public function __construct(
        private readonly CaisseService $caisseService
    ) {}

    public function aujourdhui(Request $request): JsonResponse
    {
        $user = $request->user();

        $agentReseaux = AgentReseau::where('user_id', $user->id)
            ->where('actif', true)
            ->with('reseau')
            ->get();

        $result = $agentReseaux->map(function ($agentReseau) {
            $session = CaisseSession::where('agent_reseau_id', $agentReseau->id)
                ->where('date', today())
                ->first();

            if ($session === null) {
                return [
                    'reseau' => [
                        'id' => $agentReseau->reseau->id,
                        'nom' => $agentReseau->reseau->nom,
                        'code' => $agentReseau->reseau->code,
                    ],
                    'statut' => 'inexistante',
                    'session_id' => null,
                ];
            }

            return [
                'reseau' => [
                    'id' => $agentReseau->reseau->id,
                    'nom' => $agentReseau->reseau->nom,
                    'code' => $agentReseau->reseau->code,
                ],
                'statut' => $session->statut,
                'session_id' => $session->id,
            ];
        });

        return ApiResponse::success($result, 'Sessions du jour récupérées avec succès.');
    }

    public function ouvrir(OuvrirSessionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $agentReseau = $this->caisseService->getOrCreateAgentReseau($user, $data['reseau_id']);

        $session = $this->caisseService->ouvrirSession(
            $agentReseau,
            $data['solde_float_ouverture'],
            $data['solde_cash_ouverture']
        );

        return ApiResponse::success([
            'id' => $session->id,
            'date' => $session->date,
            'solde_float_ouverture' => $session->solde_float_ouverture,
            'solde_cash_ouverture' => $session->solde_cash_ouverture,
            'statut' => $session->statut,
            'ouverte_at' => $session->ouverte_at,
        ], 'Session de caisse ouverte avec succès.', 201);
    }

    public function cloturer(CloturerSessionRequest $request, string $sessionId): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $session = CaisseSession::findOrFail($sessionId);

        $agentReseau = AgentReseau::where('id', $session->agent_reseau_id)
            ->where('user_id', $user->id)
            ->first();

        if ($agentReseau === null) {
            throw new ApiException('Cette session ne vous appartient pas.', 403);
        }

        $session = $this->caisseService->cloturerSession(
            $session,
            $data['solde_float_reel'],
            $data['solde_cash_reel'],
            $data['justification']
        );

        return ApiResponse::success([
            'id' => $session->id,
            'solde_float_theorique' => $session->solde_float_theorique,
            'solde_cash_theorique' => $session->solde_cash_theorique,
            'solde_float_reel' => $session->solde_float_reel,
            'solde_cash_reel' => $session->solde_cash_reel,
            'ecart_float' => $session->ecart_float,
            'ecart_cash' => $session->ecart_cash,
            'justification' => $session->justification,
            'statut' => $session->statut,
            'cloturee_at' => $session->cloturee_at,
        ], 'Session de caisse clôturée avec succès.');
    }
}
