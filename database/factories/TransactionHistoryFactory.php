<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionHistory>
 */
class TransactionHistoryFactory extends Factory
{
    protected $model = TransactionHistory::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'user_id' => User::factory(),
            'action' => 'CREATION',
            'anciennes_donnees' => null,
            'nouvelles_donnees' => [
                'montant' => '1000.00',
            ],
            'motif' => null,
        ];
    }
}
