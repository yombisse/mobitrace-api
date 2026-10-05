<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\AgentReseau;
use App\Models\AjustementCaisse;
use App\Models\CaisseSession;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CaisseService
{
    public function getOrCreateAgentReseau(User $user, string $reseauId): AgentReseau
    {
        return AgentReseau::firstOrCreate(
            ['user_id' => $user->id, 'reseau_id' => $reseauId],
            ['actif' => true, 'solde_float' => 0, 'solde_cash' => 0]
        );
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

    public function ouvrirSession(AgentReseau $agentReseau, float $soldeFloatOuverture, float $soldeCashOuverture): CaisseSession
    {
        $existingSession = CaisseSession::where('agent_reseau_id', $agentReseau->id)
            ->where('statut', 'ouverte')
            ->first();

        if ($existingSession !== null) {
            throw ValidationException::withMessages([
                'statut' => ['Une session est déjà ouverte pour ce réseau, veuillez la clôturer avant d\'en ouvrir une nouvelle.'],
            ]);
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
            'statut' => 'ouverte',
        ]);

        // Lier rétroactivement les transactions du jour sans session à cette nouvelle session
        // (transactions créées avant l'ouverture de session pour cet agent/reseau le même jour)
        Transaction::where('user_id', $agentReseau->user_id)
            ->where('reseau_id', $agentReseau->reseau_id)
            ->whereDate('created_at', today())
            ->whereNull('caisse_session_id')
            ->update(['caisse_session_id' => $session->id]);

        return $session;
    }

    public function cloturerSession(CaisseSession $session, float $soldeFloatReel, float $soldeCashReel, ?string $justification): CaisseSession
    {
        $agentReseau = AgentReseau::lockForUpdate()->find($session->agent_reseau_id);

        $soldeFloatTheorique = $agentReseau->solde_float;
        $soldeCashTheorique = $agentReseau->solde_cash;

        $ecartFloat = $soldeFloatTheorique - $soldeFloatReel;
        $ecartCash = $soldeCashTheorique - $soldeCashReel;

        $seuilEcart = config('caisse.seuil_ecart', 500);

        if (abs($ecartFloat) > $seuilEcart || abs($ecartCash) > $seuilEcart) {
            if (empty($justification)) {
                throw ValidationException::withMessages([
                    'justification' => ['La justification est obligatoire car l\'écart dépasse le seuil de '.$seuilEcart.'.'],
                ]);
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
            throw new ApiException('Le solde float ne peut pas être négatif après ajustement. Solde actuel : ' . number_format($agentReseau->solde_float, 0, ',', ' ') . ' FCFA.', 422);
        }

        $nouveauSoldeCash = $agentReseau->solde_cash + $montantCash;
        if ($nouveauSoldeCash < 0) {
            throw new ApiException('Le solde cash ne peut pas être négatif après ajustement. Solde actuel : ' . number_format($agentReseau->solde_cash, 0, ',', ' ') . ' FCFA.', 422);
        }

        // Vérifier que le montant n'excède pas la capacité de la base de données (999,999,999,999,999.99)
        $montantMax = 999999999999999.99;
        if (abs($montantFloat) > $montantMax) {
            throw new ApiException('Le montant float est trop élevé. Maximum autorisé : ' . number_format($montantMax, 0, ',', ' ') . ' FCFA.', 422);
        }
        if (abs($montantCash) > $montantMax) {
            throw new ApiException('Le montant cash est trop élevé. Maximum autorisé : ' . number_format($montantMax, 0, ',', ' ') . ' FCFA.', 422);
        }

        $ajustement = AjustementCaisse::create([
            'agent_reseau_id' => $agentReseau->id,
            'type' => $type,
            'montant_float' => $montantFloat,
            'montant_cash' => $montantCash,
            'motif' => $motif,
        ]);

        $this->appliquerMouvement($agentReseau, $montantFloat, $montantCash);

        // Mettre à jour le solde d'ouverture de la session pour refléter l'ajustement
        $sessionOuverte->solde_float_ouverture = $nouveauSoldeFloat;
        $sessionOuverte->solde_cash_ouverture = $nouveauSoldeCash;
        $sessionOuverte->save();

        return $ajustement;
    }

    public function appliquerEffetTransaction(Transaction $transaction, int $sens): void
    {
        DB::transaction(function () use ($transaction, $sens) {
            $agentReseau = AgentReseau::lockForUpdate()
                ->where('user_id', $transaction->user_id)
                ->where('reseau_id', $transaction->reseau_id)
                ->firstOrFail();

            $montant = $transaction->montant;
            $signe = $sens; // 1 pour appliquer, -1 pour annuler

            if ($transaction->type_operation === 'depot') {
                $deltaFloat = -$montant * $signe;
                $deltaCash = $montant * $signe;
            } else { // retrait
                $deltaFloat = $montant * $signe;
                $deltaCash = -$montant * $signe;
            }

            $agentReseau->solde_float += $deltaFloat;
            $agentReseau->solde_cash += $deltaCash;
            $agentReseau->save();
        });
    }
}
