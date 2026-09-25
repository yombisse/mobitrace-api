<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use TCPDF;

class ReleveTransactionsPdfService
{
    private const MAX_EXPORT_ROWS = 2000;
    private const CHUNK_SIZE = 200;

    public function generate(User $user, array $filters): string
    {
        try {
            // Créer le dossier temporaire s'il n'existe pas
            $tempDir = storage_path('app/mpdf');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Créer le dossier d'exports s'il n'existe pas
            $exportDir = storage_path('app/exports');
            if (! is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            // Créer l'instance TCPDF
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

            // Configuration de base
            $pdf->SetCreator('MobiTrace');
            $pdf->SetAuthor('MobiTrace');
            $pdf->SetTitle('Relevé de Transactions');
            $pdf->SetSubject('Export Transactions');
            $pdf->SetKeywords('MobiTrace, Transactions, Export');
            $pdf->SetMargins(12, 12, 12);
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetFont('dejavusans', '', 10);

            // Configurer le dossier temporaire pour TCPDF
            if (! defined('K_PATH_CACHE')) {
                define('K_PATH_CACHE', $tempDir);
            }

            // Ajouter la première page
            $pdf->AddPage();

            // Écrire l'en-tête avec CSS background-color (plus simple que TCPDF RoundedRect)
            $headerHtml = view('exports.releve-entete', [
                'agent' => $user,
                'debut' => \Carbon\Carbon::parse($filters['debut']),
                'fin' => \Carbon\Carbon::parse($filters['fin']),
                'reseau' => $filters['reseau'] ?? null,
                'generatedAt' => now(),
            ])->render();
            $pdf->writeHTML($headerHtml, true, false, true, false, '');

            // Récupérer les transactions avec génération par lots
            $query = Transaction::query()
                ->where('user_id', $user->id)
                ->select('*')
                ->with(['client:id,telephone,nom,prenoms,numero_piece,date_delivrance_piece,date_expiration_piece,photo', 'reseau:id,code,nom'])
                ->where('created_at', '>=', $filters['debut'])
                ->where('created_at', '<=', $filters['fin'].' 23:59:59')
                ->orderBy('created_at', 'asc');

            if (! empty($filters['reseau'])) {
                Log::info('Filtrage par réseau', [
                    'reseau_code_received' => $filters['reseau'],
                    'user_id' => $user->id,
                ]);

                $reseau = \App\Models\Reseau::whereRaw('LOWER(code) = ?', [strtolower($filters['reseau'])])->first();
                if ($reseau) {
                    $query->where('reseau_id', $reseau->id);
                    Log::info('Réseau trouvé', [
                        'reseau_id' => $reseau->id,
                        'reseau_code' => $reseau->code,
                        'reseau_nom' => $reseau->nom,
                    ]);
                } else {
                    Log::warning('Réseau non trouvé pour l\'export', [
                        'reseau_code_requested' => $filters['reseau'],
                        'available_reseaux' => \App\Models\Reseau::pluck('code')->toArray(),
                        'user_id' => $user->id,
                    ]);
                }
            }

            // Compter le total pour vérifier la limite
            $totalCount = $query->count();
            if ($totalCount > self::MAX_EXPORT_ROWS) {
                throw new \Exception("L'export contient {$totalCount} transactions. La limite est de ".self::MAX_EXPORT_ROWS.' lignes. Veuillez réduire la période.');
            }

            if ($totalCount === 0) {
                throw new \Exception('Aucune transaction trouvée pour cette période.');
            }

            // Utiliser une taille de police fixe raisonnable pour le corps du tableau
            $optimalFontSize = 7.5;
            $pdf->SetFont('dejavusans', '', $optimalFontSize);

            // Calculer les totaux
            $totalMontant = (clone $query)->sum('montant');
            $totalDepots = (clone $query)->where('type_operation', 'depot')->sum('montant');
            $totalRetraits = (clone $query)->where('type_operation', 'retrait')->sum('montant');
            $soldeNet = $totalDepots - $totalRetraits;
            $hasSoldeApresOperation = (clone $query)->whereNotNull('solde_apres_operation')->exists();

            // Écrire les totaux AVANT le tableau
            $totalHtml = view('exports.releve-total', [
                'totalMontant' => $totalMontant,
                'totalDepots' => $totalDepots,
                'totalRetraits' => $totalRetraits,
                'soldeNet' => $soldeNet,
                'count' => $totalCount,
            ])->render();
            $pdf->writeHTML($totalHtml, true, false, true, false, '');

            // Générer tout le tableau en une seule fois pour éviter la répétition des en-têtes
            $allTransactions = $query->get();
            $rowsHtml = view('exports.releve-lignes', [
                'transactions' => $allTransactions,
                'hasSoldeApresOperation' => $hasSoldeApresOperation,
            ])->render();
            $pdf->writeHTML($rowsHtml, false, false, true, false, '');

            // Générer le nom de fichier
            $filename = 'releve-transactions_'.$filters['debut'].'_'.$filters['fin'].'.pdf';
            $filepath = $exportDir.'/'.$filename;

            // Sauvegarder le PDF
            $pdf->Output($filepath, 'F');

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
