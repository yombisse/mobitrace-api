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

    public function sessionOuverteDuJour(AgentReseau $agentReseau): ?CaisseSession
    {
        return CaisseSession::where('agent_reseau_id', $agentReseau->id)
            ->where('date', today())
            ->where('statut', 'ouverte')
            ->first();
    }

    public function ouvrirSession(AgentReseau $agentReseau, float $soldeFloatOuverture, float $soldeCashOuverture): CaisseSession
    {
        $existingSession = CaisseSession::where('agent_reseau_id', $agentReseau->id)
            ->where('date', today())
            ->first();

        if ($existingSession !== null) {
            throw ValidationException::withMessages([
                'date' => ['Une session existe déjà pour ce réseau aujourd\'hui.'],
            ]);
        }

        return CaisseSession::create([
            'agent_reseau_id' => $agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => $soldeFloatOuverture,
            'solde_cash_ouverture' => $soldeCashOuverture,
            'statut' => 'ouverte',
        ]);
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
