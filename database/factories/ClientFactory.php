<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'telephone' => fake()->unique()->numerify('########'),
            'nom' => fake()->lastName(),
            'prenoms' => fake()->firstName(),
            'date_naissance' => fake()->date(),
            'nationalite' => 'Burkinabè',
            'type_piece' => 'CNIB',
            'numero_piece' => fake()->unique()->bothify('########'),
            'date_expiration_piece' => fake()->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
        ];
    }
}
