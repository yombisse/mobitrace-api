<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\Caisse\CloturerSessionRequest;
use App\Http\Requests\Caisse\OuvrirSessionRequest;
use App\Models\AgentReseau;
use App\Models\AjustementCaisse;
use App\Models\CaisseSession;
use App\Models\Transaction;
use App\Services\CaisseService;
use App\Services\SessionCaissePdfService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CaisseSessionController extends Controller
{
    public function __construct(
        private readonly CaisseService $caisseService,
        private readonly SessionCaissePdfService $pdfService
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
                ->where('statut', 'ouverte')
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
            $data['solde_cash_ouverture'],
            $data['justification'] ?? null
        );

        $response = [
            'id' => $session->id,
            'date' => $session->date,
            'solde_float_ouverture' => $session->solde_float_ouverture,
            'solde_cash_ouverture' => $session->solde_cash_ouverture,
            'statut' => $session->statut,
            'ouverte_at' => $session->ouverte_at,
        ];

        if (isset($session->meta)) {
            $response['meta'] = $session->meta;
        }

        return ApiResponse::success($response, 'Session de caisse ouverte avec succès.', 201);
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
            $data['justification'] ?? null
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

    public function historique(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = CaisseSession::whereHas('agentReseau', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('statut', 'cloturee')
            ->with(['agentReseau.reseau'])
            ->orderBy('cloturee_at', 'desc');

        // Filtres optionnels
        if ($request->has('reseau_id')) {
            $query->whereHas('agentReseau', function ($query) use ($request) {
                $query->where('reseau_id', $request->input('reseau_id'));
            });
        }

        if ($request->has('date_start')) {
            $query->where('date', '>=', $request->input('date_start'));
        }

        if ($request->has('date_end')) {
            $query->where('date', '<=', $request->input('date_end'));
        }

        $limit = $request->input('limit', 20);
        $sessions = $query->limit($limit)->get();

        $result = $sessions->map(function ($session) {
            return [
                'id' => $session->id,
                'date' => $session->date,
                'reseau' => [
                    'id' => $session->agentReseau->reseau->id,
                    'nom' => $session->agentReseau->reseau->nom,
                    'code' => $session->agentReseau->reseau->code,
                ],
                'solde_float_ouverture' => $session->solde_float_ouverture,
                'solde_cash_ouverture' => $session->solde_cash_ouverture,
                'solde_float_theorique' => $session->solde_float_theorique,
                'solde_cash_theorique' => $session->solde_cash_theorique,
                'solde_float_reel' => $session->solde_float_reel,
                'solde_cash_reel' => $session->solde_cash_reel,
                'ecart_float' => $session->ecart_float,
                'ecart_cash' => $session->ecart_cash,
                'statut' => $session->statut,
                'ouverte_at' => $session->ouverte_at,
                'cloturee_at' => $session->cloturee_at,
            ];
        });

        return ApiResponse::success($result, 'Historique des sessions récupéré avec succès.');
    }

    public function bilan(Request $request, string $sessionId): JsonResponse
    {
        $user = $request->user();

        $session = CaisseSession::with(['agentReseau.reseau'])
            ->findOrFail($sessionId);

        // Vérifier que la session appartient à l'utilisateur
        if ($session->agentReseau->user_id !== $user->id) {
            throw new ApiException('Cette session ne vous appartient pas.', 403);
        }

        // Récupérer les transactions de la session
        $transactions = Transaction::where('caisse_session_id', $session->id)
            ->with('client')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type_operation' => $transaction->type_operation,
                    'montant' => $transaction->montant,
                    'reference' => $transaction->reference,
                    'client' => [
                        'id' => $transaction->client->id,
                        'nom' => $transaction->client->nom,
                        'prenoms' => $transaction->client->prenoms,
                        'telephone' => $transaction->client->telephone,
                    ],
                    'created_at' => $transaction->created_at,
                ];
            });

        // Récupérer les ajustements de la session
        $ajustements = AjustementCaisse::where('agent_reseau_id', $session->agent_reseau_id)
            ->whereBetween('created_at', [$session->ouverte_at, $session->cloturee_at ?? now()])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($ajustement) {
                return [
                    'id' => $ajustement->id,
                    'type' => $ajustement->type,
                    'montant_float' => $ajustement->montant_float,
                    'montant_cash' => $ajustement->montant_cash,
                    'motif' => $ajustement->motif,
                    'created_at' => $ajustement->created_at,
                ];
            });

        // Calculer le résumé
        $totalDepots = $transactions->where('type_operation', 'depot')->sum('montant');
        $totalRetraits = $transactions->where('type_operation', 'retrait')->sum('montant');
        $totalAjustementsFloat = $ajustements->sum('montant_float');
        $totalAjustementsCash = $ajustements->sum('montant_cash');

        // Calculer le solde théorique dynamiquement si session ouverte
        $soldeFloatTheorique = $session->solde_float_theorique;
        $soldeCashTheorique = $session->solde_cash_theorique;
        if ($session->statut === 'ouverte') {
            $soldesTheoriques = $this->caisseService->calculerSoldesTheoriques($session);
            $soldeFloatTheorique = $soldesTheoriques['solde_float_theorique'];
            $soldeCashTheorique = $soldesTheoriques['solde_cash_theorique'];
        }

        return ApiResponse::success([
            'session' => [
                'id' => $session->id,
                'date' => $session->date,
                'reseau' => [
                    'id' => $session->agentReseau->reseau->id,
                    'nom' => $session->agentReseau->reseau->nom,
                    'code' => $session->agentReseau->reseau->code,
                ],
                'solde_float_ouverture' => $session->solde_float_ouverture,
                'solde_cash_ouverture' => $session->solde_cash_ouverture,
                'ecart_float_ouverture' => $session->ecart_float_ouverture,
                'ecart_cash_ouverture' => $session->ecart_cash_ouverture,
                'justification_ouverture' => $session->justification_ouverture,
                'solde_float_theorique' => $soldeFloatTheorique,
                'solde_cash_theorique' => $soldeCashTheorique,
                'solde_float_reel' => $session->solde_float_reel,
                'solde_cash_reel' => $session->solde_cash_reel,
                'ecart_float' => $session->ecart_float,
                'ecart_cash' => $session->ecart_cash,
                'justification' => $session->justification,
                'statut' => $session->statut,
                'ouverte_at' => $session->ouverte_at,
                'cloturee_at' => $session->cloturee_at,
            ],
            'transactions' => $transactions,
            'ajustements' => $ajustements,
            'resume' => [
                'nombre_transactions' => $transactions->count(),
                'total_depots' => $totalDepots,
                'total_retraits' => $totalRetraits,
                'nombre_ajustements' => $ajustements->count(),
                'total_ajustements_float' => $totalAjustementsFloat,
                'total_ajustements_cash' => $totalAjustementsCash,
            ],
        ], 'Bilan de session récupéré avec succès.');
    }

    public function exportPdf(Request $request, string $sessionId)
    {
        $user = $request->user();

        try {
            $filepath = $this->pdfService->generateBilanSession($user, $sessionId);

            return Response::download($filepath)->deleteFileAfterSend(true);
        } catch (\Exception) {
            throw new ApiException('Impossible de générer le PDF de session.', 422);
        }
    }
}
