<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'reseau_id' => Reseau::factory(),
            'type_operation' => fake()->randomElement(['depot', 'retrait']),
            'montant' => fake()->randomFloat(2, 100, 100000),
            'reference' => fake()->unique()->bothify('OM-######-####'),
            'solde_apres_operation' => fake()->randomFloat(2, 100, 1000000),
            'note' => null,
            'statut' => 'ENREGISTREE',
            'sync_status' => 'SYNCED',
            'version' => 1,
            'synced_at' => now(),
            'consentement_recap' => fake()->sentence(),
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
        ];
    }
}
