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
                'date_naissance' => '1985-05-15',
                'type_piece' => 'CNIB',
                'numero_piece' => 'B12345678',
                'date_delivrance_piece' => '2015-05-15',
                'date_expiration_piece' => '2025-05-15',
                'cni_recto' => 'https://i.pravatar.cc/150?u=client1',
                'cni_verso' => 'https://i.pravatar.cc/150?u=client1_verso',
            ]
        );

        $client2 = Client::firstOrCreate(
            ['telephone' => '70000002'],
            [
                'user_id' => $user1->id,
                'nom' => 'OUEDRAOGO',
                'prenoms' => 'Mariam',
                'nationalite' => 'Burkinabè',
                'date_naissance' => '1990-08-20',
                'type_piece' => 'CNIB',
                'numero_piece' => 'B87654321',
                'date_delivrance_piece' => '2018-08-20',
                'date_expiration_piece' => '2028-08-20',
                'cni_recto' => 'https://i.pravatar.cc/150?u=client2',
                'cni_verso' => 'https://i.pravatar.cc/150?u=client2_verso',
            ]
        );

        $client3 = Client::firstOrCreate(
            ['telephone' => '22606913192'],
            [
                'user_id' => $user2->id,
                'nom' => 'DIALLO',
                'prenoms' => 'Ibrahim',
                'nationalite' => 'Burkinabè',
                'date_naissance' => '1988-03-10',
                'type_piece' => 'CNIB',
                'numero_piece' => 'B56789012',
                'date_delivrance_piece' => '2016-03-10',
                'date_expiration_piece' => '2026-03-10',
                'cni_recto' => 'https://i.pravatar.cc/150?u=client3',
                'cni_verso' => 'https://i.pravatar.cc/150?u=client3_verso',
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

        // Ajouter des transactions supplémentaires pour tester la pagination
        for ($i = 4; $i <= 15; $i++) {
            $client = $i % 2 === 0 ? $client1 : $client2;
            $reseau = $i % 3 === 0 ? $wave : ($i % 2 === 0 ? $moovMoney : $orangeMoney);
            $type = $i % 2 === 0 ? 'retrait' : 'depot';
            $montant = rand(10000, 100000);
            $solde = $montant + rand(5000, 50000);

            Transaction::create([
                'user_id' => $user1->id,
                'client_id' => $client->id,
                'reseau_id' => $reseau->id,
                'type_operation' => $type,
                'montant' => $montant,
                'reference' => strtoupper($reseau->code).'-20250914-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'solde_apres_operation' => $solde,
                'note' => 'Transaction de test '.$i,
                'statut' => 'ENREGISTREE',
                'sync_status' => 'SYNCED',
                'version' => 1,
                'synced_at' => now(),
                'consentement_recap' => ucfirst($type).' de '.$montant.' FCFA pour le '.$client->telephone.' via '.$reseau->nom,
                'consentement_methode' => 'confirmation_client',
                'consentement_confirme_le' => now(),
            ]);
        }

        $this->command->info('15 transactions de test ont été créées avec succès.');
    }
}
