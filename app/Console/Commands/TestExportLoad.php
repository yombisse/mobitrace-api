<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('app:test-export-load')]
#[Description('Test export PDF load with 1000 and 2000 transactions')]
class TestExportLoad extends Command
{
    public function handle()
    {
        $this->info('=== Test de charge pour l\'export PDF ===');

        // Créer un utilisateur de test
        $user = User::first();
        if (! $user) {
            $this->error('Aucun utilisateur trouvé. Exécutez php artisan db:seed d\'abord.');
            return 1;
        }

        // Créer un réseau de test
        $reseau = Reseau::first();
        if (! $reseau) {
            $this->error('Aucun réseau trouvé. Exécutez php artisan db:seed d\'abord.');
            return 1;
        }

        // Créer un client de test
        $client = Client::where('user_id', $user->id)->first();
        if (! $client) {
            $this->error('Aucun client trouvé pour cet utilisateur. Exécutez php artisan db:seed d\'abord.');
            return 1;
        }

        foreach ([100, 500] as $count) {
            $this->newLine();
            $this->info("Test avec {$count} transactions...");

            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            // Générer les transactions
            DB::beginTransaction();
            try {
                for ($i = 0; $i < $count; $i++) {
                    Transaction::create([
                        'id' => (string) Str::uuid(),
                        'user_id' => $user->id,
                        'client_id' => $client->id,
                        'reseau_id' => $reseau->id,
                        'type_operation' => $i % 2 === 0 ? 'depot' : 'retrait',
                        'montant' => rand(1000, 100000),
                        'reference' => 'TEST-'.str_pad($i, 6, '0', STR_PAD_LEFT),
                        'solde_apres_operation' => rand(0, 1000000),
                        'statut' => 'ENREGISTREE',
                        'version' => 1,
                        'sync_status' => 'SYNCED',
                        'consentement_recap' => 'Test transaction '.$i,
                        'consentement_methode' => 'confirmation_client',
                        'consentement_confirme_le' => now()->subDays(rand(1, 30)),
                        'created_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Erreur lors de la création des transactions: {$e->getMessage()}");
                continue;
            }

            $insertTime = microtime(true) - $startTime;
            $insertMemory = memory_get_usage() - $startMemory;

            $this->info("✓ {$count} transactions créées en ".number_format($insertTime, 3).'s');
            $this->info("  Mémoire utilisée: ".number_format($insertMemory / 1024 / 1024, 2).' MB');
            $this->info("  Mémoire pic: ".number_format(memory_get_peak_usage() / 1024 / 1024, 2).' MB');

            // Nettoyer pour le prochain test
            Transaction::where('reference', 'like', 'TEST-%')->delete();
        }

        $this->newLine();
        $this->info('=== Test terminé ===');

        return 0;
    }
}
