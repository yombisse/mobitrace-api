<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\AgentReseau;
use App\Models\AjustementCaisse;
use App\Models\CaisseSession;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CaisseService
{
    public function getOrCreateAgentReseau(User $user, string $reseauId): AgentReseau
    {
        Reseau::where('owner_user_id', $user->getKey())->findOrFail($reseauId);
        $agentReseau = AgentReseau::firstOrCreate(
            ['user_id' => $user->id, 'reseau_id' => $reseauId],
            ['actif' => true, 'solde_float' => 0, 'solde_cash' => 0]
        );

        if (! $agentReseau->actif) {
            throw new ApiException('Ce réseau est désactivé.', 422);
        }

        return $agentReseau;
    }

    public function appliquerMouvement(AgentReseau $agentReseau, float $deltaFloat, float $deltaCash): void
    {
        DB::transaction(function () use ($agentReseau, $deltaFloat, $deltaCash) {
            $agentReseau = AgentReseau::lockForUpdate()->find($agentReseau->id);
            $agentReseau->solde_float += $deltaFloat;
            $agentReseau->solde_cash += $deltaCash;
            $agentReseau->save();
        });
    }

    public function sessionOuverte(AgentReseau $agentReseau): ?CaisseSession
    {
        return CaisseSession::where('agent_reseau_id', $agentReseau->id)
            ->where('statut', 'ouverte')
            ->first();
    }

    public function ouvrirSession(AgentReseau $agentReseau, float $soldeFloatOuverture, float $soldeCashOuverture, ?string $justification = null): CaisseSession
    {
        return DB::transaction(function () use ($agentReseau, $soldeFloatOuverture, $soldeCashOuverture, $justification) {
            $agentReseau = AgentReseau::lockForUpdate()->find($agentReseau->id);

            $existingSession = CaisseSession::where('agent_reseau_id', $agentReseau->id)
                ->where('statut', 'ouverte')
                ->first();

            if ($existingSession !== null) {
                throw ValidationException::withMessages([
                    'statut' => ['Une session est déjà ouverte pour ce réseau, veuillez la clôturer avant d\'en ouvrir une nouvelle.'],
                ]);
            }

            // Récupérer la dernière session clôturée pour la référence
            $derniereSessionCloturee = CaisseSession::where('agent_reseau_id', $agentReseau->id)
                ->where('statut', 'cloturee')
                ->orderBy('cloturee_at', 'desc')
                ->first();

            // Solde de référence : solde_reel de la dernière clôture, ou null si aucune
            $soldeFloatReference = $derniereSessionCloturee?->solde_float_reel;
            $soldeCashReference = $derniereSessionCloturee?->solde_cash_reel;

            // Calculer les écarts par rapport à la référence
            $ecartFloatOuverture = $soldeFloatReference !== null ? $soldeFloatOuverture - $soldeFloatReference : null;
            $ecartCashOuverture = $soldeCashReference !== null ? $soldeCashOuverture - $soldeCashReference : null;

            // Vérifier si justification requise (écart > seuil)
            $seuilEcart = config('caisse.seuil_ecart', 500);
            $justificationRequise = false;

            if ($soldeFloatReference !== null) {
                if (($ecartFloatOuverture !== null && abs($ecartFloatOuverture) > $seuilEcart) ||
                    ($ecartCashOuverture !== null && abs($ecartCashOuverture) > $seuilEcart)) {
                    $justificationRequise = true;
                }
            }

            if ($justificationRequise && empty($justification)) {
                throw new ApiException(
                    'La justification est obligatoire car l\'écart dépasse le seuil de '.$seuilEcart.'.',
                    422,
                    [
                        'justification' => ['La justification est obligatoire car l\'écart dépasse le seuil de '.$seuilEcart.'.'],
                    ],
                    [
                        'solde_float_precedent' => $soldeFloatReference,
                        'solde_cash_precedent' => $soldeCashReference,
                        'solde_float_declare' => $soldeFloatOuverture,
                        'solde_cash_declare' => $soldeCashOuverture,
                        'ecart_float' => $ecartFloatOuverture,
                        'ecart_cash' => $ecartCashOuverture,
                        'seuil_ecart' => $seuilEcart,
                    ]
                );
            }

            // Mettre à jour les soldes de l'agent_reseau avec les soldes d'ouverture
            $agentReseau->solde_float = $soldeFloatOuverture;
            $agentReseau->solde_cash = $soldeCashOuverture;
            $agentReseau->save();

            $session = CaisseSession::create([
                'agent_reseau_id' => $agentReseau->id,
                'date' => today(),
                'solde_float_ouverture' => $soldeFloatOuverture,
                'solde_cash_ouverture' => $soldeCashOuverture,
                'ecart_float_ouverture' => $ecartFloatOuverture,
                'ecart_cash_ouverture' => $ecartCashOuverture,
                'justification_ouverture' => $justification,
                'statut' => 'ouverte',
            ]);

            // Ajouter meta à la session pour la réponse
            $session->meta = [
                'solde_float_precedent' => $soldeFloatReference,
                'solde_cash_precedent' => $soldeCashReference,
                'ecart_float' => $ecartFloatOuverture,
                'ecart_cash' => $ecartCashOuverture,
            ];

            Log::info('Ouverture session caisse', [
                'session_id' => $session->id,
                'user_id' => $agentReseau->user_id,
            ]);

            return $session;
        });
    }

    /**
     * @return array{solde_float_theorique: float, solde_cash_theorique: float}
     */
    public function calculerSoldesTheoriques(CaisseSession $session): array
    {
        $soldeFloatTheorique = $session->solde_float_ouverture;
        $soldeCashTheorique = $session->solde_cash_ouverture;

        // Ajouter les transactions de la session au solde théorique (hors ANNULEE)
        $transactions = Transaction::where('caisse_session_id', $session->id)
            ->where('statut', '!=', 'ANNULEE')
            ->get();
        foreach ($transactions as $transaction) {
            $delta = $this->calculerDeltaTransaction($transaction);
            $soldeFloatTheorique += $delta['float'];
            $soldeCashTheorique += $delta['cash'];
        }

        // Ajouter les ajustements de la session au solde théorique
        $ajustements = AjustementCaisse::where('agent_reseau_id', $session->agent_reseau_id)
            ->whereBetween('created_at', [$session->ouverte_at, $session->cloturee_at ?? now()])
            ->get();
        foreach ($ajustements as $ajustement) {
            $soldeFloatTheorique += $ajustement->montant_float;
            $soldeCashTheorique += $ajustement->montant_cash;
        }

        return [
            'solde_float_theorique' => $soldeFloatTheorique,
            'solde_cash_theorique' => $soldeCashTheorique,
        ];
    }

    /**
     * @return array{float: float, cash: float}
     */
    private function calculerDeltaTransaction(Transaction $transaction): array
    {
        if ($transaction->type_operation === 'depot') {
            return ['float' => -$transaction->montant, 'cash' => $transaction->montant];
        } else { // retrait
            return ['float' => $transaction->montant, 'cash' => -$transaction->montant];
        }
    }

    public function cloturerSession(CaisseSession $session, float $soldeFloatReel, float $soldeCashReel, ?string $justification): CaisseSession
    {
        // Refuser de re-clôturer une session déjà clôturée
        if ($session->statut === 'cloturee') {
            throw new ApiException('Cette session est déjà clôturée.', 422);
        }

        $agentReseau = AgentReseau::lockForUpdate()->find($session->agent_reseau_id);

        $soldesTheoriques = $this->calculerSoldesTheoriques($session);
        $soldeFloatTheorique = $soldesTheoriques['solde_float_theorique'];
        $soldeCashTheorique = $soldesTheoriques['solde_cash_theorique'];

        $ecartFloat = $soldeFloatTheorique - $soldeFloatReel;
        $ecartCash = $soldeCashTheorique - $soldeCashReel;

        $seuilEcart = config('caisse.seuil_ecart', 500);

        if (abs($ecartFloat) > $seuilEcart || abs($ecartCash) > $seuilEcart) {
            if (empty($justification)) {
                throw new ApiException(
                    'La justification est obligatoire car l\'écart dépasse le seuil de '.$seuilEcart.'.'.
                    422,
                    [
                        'justification' => ['La justification est obligatoire car l\'écart dépasse le seuil de '.$seuilEcart.'.'],
                    ],
                    [
                        'solde_float_theorique' => $soldeFloatTheorique,
                        'solde_cash_theorique' => $soldeCashTheorique,
                        'solde_float_reel' => $soldeFloatReel,
                        'solde_cash_reel' => $soldeCashReel,
                        'ecart_float' => $ecartFloat,
                        'ecart_cash' => $ecartCash,
                        'seuil_ecart' => $seuilEcart,
                    ]
                );
            }
        }

        $session->update([
            'solde_float_theorique' => $soldeFloatTheorique,
            'solde_cash_theorique' => $soldeCashTheorique,
            'solde_float_reel' => $soldeFloatReel,
            'solde_cash_reel' => $soldeCashReel,
            'ecart_float' => $ecartFloat,
            'ecart_cash' => $ecartCash,
            'justification' => $justification,
            'statut' => 'cloturee',
            'cloturee_at' => now(),
        ]);

        return $session->refresh();
    }

    public function ajusterCaisse(AgentReseau $agentReseau, string $type, float $montantFloat, float $montantCash, string $motif): AjustementCaisse
    {
        // Vérifier qu'une session est ouverte pour cet agent_reseau
        $sessionOuverte = $this->sessionOuverte($agentReseau);
        if ($sessionOuverte === null) {
            throw new ApiException('Aucune session de caisse ouverte pour ce réseau. Veuillez ouvrir une session avant d\'ajuster les soldes.', 422);
        }

        // Vérifier que le solde final ne sera pas négatif
        $nouveauSoldeFloat = $agentReseau->solde_float + $montantFloat;
        if ($nouveauSoldeFloat < 0) {
            throw new ApiException('Le solde float ne peut pas être négatif après ajustement. Solde actuel : '.number_format($agentReseau->solde_float, 0, ',', ' ').' FCFA.', 422);
        }

        $nouveauSoldeCash = $agentReseau->solde_cash + $montantCash;
        if ($nouveauSoldeCash < 0) {
            throw new ApiException('Le solde cash ne peut pas être négatif après ajustement. Solde actuel : '.number_format($agentReseau->solde_cash, 0, ',', ' ').' FCFA.', 422);
        }

        // Vérifier que le montant n'excède pas la capacité de la base de données (999,999,999,999,999.99)
        $montantMax = 999999999999999.99;
        if (abs($montantFloat) > $montantMax) {
            throw new ApiException('Le montant float est trop élevé. Maximum autorisé : '.number_format($montantMax, 0, ',', ' ').' FCFA.', 422);
        }
        if (abs($montantCash) > $montantMax) {
            throw new ApiException('Le montant cash est trop élevé. Maximum autorisé : '.number_format($montantMax, 0, ',', ' ').' FCFA.', 422);
        }

        $ajustement = AjustementCaisse::create([
            'agent_reseau_id' => $agentReseau->id,
            'type' => $type,
            'montant_float' => $montantFloat,
            'montant_cash' => $montantCash,
            'motif' => $motif,
        ]);

        $this->appliquerMouvement($agentReseau, $montantFloat, $montantCash);

        return $ajustement;
    }

    public function appliquerEffetTransaction(Transaction $transaction, int $sens): void
    {
        // Ne pas appliquer l'effet si la transaction n'a pas de session (historique pré-caisse)
        if ($transaction->caisse_session_id === null) {
            return;
        }

        DB::transaction(function () use ($transaction, $sens) {
            $agentReseau = AgentReseau::lockForUpdate()
                ->where('user_id', $transaction->user_id)
                ->where('reseau_id', $transaction->reseau_id)
                ->firstOrFail();

            $delta = $this->calculerDeltaTransaction($transaction);
            $signe = $sens; // 1 pour appliquer, -1 pour annuler

            $agentReseau->solde_float += $delta['float'] * $signe;
            $agentReseau->solde_cash += $delta['cash'] * $signe;
            $agentReseau->save();
        });
    }
}
