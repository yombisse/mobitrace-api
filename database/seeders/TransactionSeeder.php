<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les utilisateurs existants
        $user1 = User::where('telephone', '70000000')->first();
        $user2 = User::where('telephone', '22606913191')->first();

        // Récupérer les réseaux existants
        $orangeMoney = Reseau::where('code', 'OM')->first();
        $moovMoney = Reseau::where('code', 'MV')->first();
        $wave = Reseau::where('code', 'WA')->first();

        if (!$user1 || !$user2 || !$orangeMoney || !$moovMoney || !$wave) {
            $this->command->warn('Certains modèles requis sont manquants. Veuillez exécuter DatabaseSeeder d\'abord.');
            return;
        }

        // Créer des clients pour les transactions
        $client1 = Client::firstOrCreate(
            ['telephone' => '70000001'],
            [
                'user_id' => $user1->id,
                'nom' => 'KABORE',
                'prenoms' => 'Jean',
                'nationalite' => 'Burkinabè',
            ]
        );

        $client2 = Client::firstOrCreate(
            ['telephone' => '70000002'],
            [
                'user_id' => $user1->id,
                'nom' => 'OUEDRAOGO',
                'prenoms' => 'Mariam',
                'nationalite' => 'Burkinabè',
            ]
        );

        $client3 = Client::firstOrCreate(
            ['telephone' => '22606913192'],
            [
                'user_id' => $user2->id,
                'nom' => 'DIALLO',
                'prenoms' => 'Ibrahim',
                'nationalite' => 'Burkinabè',
            ]
        );

        // Créer les transactions de test
        Transaction::create([
            'user_id' => $user1->id,
            'client_id' => $client1->id,
            'reseau_id' => $orangeMoney->id,
            'type_operation' => 'depot',
            'montant' => 50000.00,
            'reference' => 'OM-20250914-0001',
            'solde_apres_operation' => 50000.00,
            'note' => 'Dépot initial',
            'statut' => 'ENREGISTREE',
            'sync_status' => 'SYNCED',
            'version' => 1,
            'synced_at' => now(),
            'consentement_recap' => 'Dépôt de 50000 FCFA pour le 70000001 via Orange Money',
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
        ]);

        Transaction::create([
            'user_id' => $user1->id,
            'client_id' => $client2->id,
            'reseau_id' => $moovMoney->id,
            'type_operation' => 'retrait',
            'montant' => 25000.00,
            'reference' => 'MV-20250914-0002',
            'solde_apres_operation' => 75000.00,
            'note' => 'Retrait pour achat',
            'statut' => 'ENREGISTREE',
            'sync_status' => 'SYNCED',
            'version' => 1,
            'synced_at' => now(),
            'consentement_recap' => 'Retrait de 25000 FCFA pour le 70000002 via Moov Money',
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
        ]);

        Transaction::create([
            'user_id' => $user2->id,
            'client_id' => $client3->id,
            'reseau_id' => $wave->id,
            'type_operation' => 'depot',
            'montant' => 100000.00,
            'reference' => 'WA-20250914-0003',
            'solde_apres_operation' => 100000.00,
            'note' => 'Dépot important',
            'statut' => 'ENREGISTREE',
            'sync_status' => 'SYNCED',
            'version' => 1,
            'synced_at' => now(),
            'consentement_recap' => 'Dépôt de 100000 FCFA pour le 22606913192 via Wave',
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
        ]);

        // Ajouter une transaction supplémentaire pour plus de tests
        Transaction::create([
            'user_id' => $user1->id,
            'client_id' => $client1->id,
            'reseau_id' => $orangeMoney->id,
            'type_operation' => 'depot',
            'montant' => 15000.00,
            'reference' => 'OM-20250914-0004',
            'solde_apres_operation' => 65000.00,
            'note' => 'Dépot supplémentaire',
            'statut' => 'ENREGISTREE',
            'sync_status' => 'SYNCED',
            'version' => 1,
            'synced_at' => now(),
            'consentement_recap' => 'Dépôt de 15000 FCFA pour le 70000001 via Orange Money',
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
        ]);

        $this->command->info('4 transactions de test ont été créées avec succès.');
    }
}
