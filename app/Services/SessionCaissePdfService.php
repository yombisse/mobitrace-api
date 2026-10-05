<?php

namespace App\Services;

use App\Models\AjustementCaisse;
use App\Models\CaisseSession;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use TCPDF;

class SessionCaissePdfService
{
    public function generateBilanSession(User $user, string $sessionId): string
    {
        try {
            $session = CaisseSession::with(['agentReseau.reseau', 'agentReseau.user'])
                ->findOrFail($sessionId);

            // Vérifier que la session appartient à l'utilisateur
            if ($session->agentReseau->user_id !== $user->id) {
                throw new \Exception('Cette session ne vous appartient pas.');
            }

            // Créer le dossier d'exports s'il n'existe pas
            $exportDir = storage_path('app/exports');
            if (! is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            // Créer l'instance TCPDF - Orientation PORTRAIT
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Configuration de base
            $pdf->SetCreator('MobiTrace');
            $pdf->SetAuthor('MobiTrace');
            $pdf->SetTitle('Bilan de Session de Caisse');
            $pdf->SetSubject('Export Session Caisse');
            $pdf->SetKeywords('MobiTrace, Session, Caisse, Export');
            $pdf->SetMargins(12, 12, 12);
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetFont('dejavusans', '', 10);

            // Ajouter la première page
            $pdf->AddPage();

            // Écrire l'en-tête
            $headerHtml = view('exports.session-entete', [
                'session' => $session,
                'generatedAt' => now(),
            ])->render();
            $pdf->writeHTML($headerHtml, true, false, true, false, '');

            // Récupérer les transactions de la session
            $transactions = Transaction::where('caisse_session_id', $session->id)
                ->with('client')
                ->orderBy('created_at', 'asc')
                ->get();

            // Récupérer les ajustements de la session
            $ajustements = AjustementCaisse::where('agent_reseau_id', $session->agent_reseau_id)
                ->whereBetween('created_at', [$session->ouverte_at, $session->cloturee_at ?? now()])
                ->orderBy('created_at', 'asc')
                ->get();

            // Calculer les totaux
            $totalDepots = $transactions->where('type_operation', 'depot')->sum('montant');
            $totalRetraits = $transactions->where('type_operation', 'retrait')->sum('montant');
            $totalAjustementsFloat = $ajustements->sum('montant_float');
            $totalAjustementsCash = $ajustements->sum('montant_cash');

            // Écrire le résumé
            $resumeHtml = view('exports.session-resume', [
                'session' => $session,
                'totalDepots' => $totalDepots,
                'totalRetraits' => $totalRetraits,
                'totalAjustementsFloat' => $totalAjustementsFloat,
                'totalAjustementsCash' => $totalAjustementsCash,
                'nombreTransactions' => $transactions->count(),
                'nombreAjustements' => $ajustements->count(),
            ])->render();
            $pdf->writeHTML($resumeHtml, true, false, true, false, '');

            // Utiliser une taille de police fixe pour le corps du tableau
            $pdf->SetFont('dejavusans', '', 7.5);

            // Écrire les transactions
            if ($transactions->isNotEmpty()) {
                $transactionsHtml = view('exports.session-transactions', [
                    'transactions' => $transactions,
                ])->render();
                $pdf->writeHTML($transactionsHtml, true, false, true, false, '');
            }

            // Écrire les ajustements
            if ($ajustements->isNotEmpty()) {
                $ajustementsHtml = view('exports.session-ajustements', [
                    'ajustements' => $ajustements,
                ])->render();
                $pdf->writeHTML($ajustementsHtml, true, false, true, false, '');
            }

            // Générer le nom de fichier
            $filename = 'bilan-session-'.$session->id.'-'.$session->date.'.pdf';
            $filepath = $exportDir.'/'.$filename;

            // Sauvegarder le PDF
            $pdf->Output($filepath, 'F');

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF de session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
