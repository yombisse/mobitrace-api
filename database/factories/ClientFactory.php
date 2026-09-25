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
        $dateExpiration = fake()->dateTimeBetween('+1 year', '+5 years');
        $dateDelivrance = (clone $dateExpiration)->modify('-10 years');

        return [
            'user_id' => User::factory(),
            'telephone' => fake()->unique()->numerify('########'),
            'nom' => fake()->lastName(),
            'prenoms' => fake()->firstName(),
            'date_naissance' => fake()->date(),
            'nationalite' => 'Burkinabè',
            'type_piece' => 'CNIB',
            'numero_piece' => fake()->unique()->bothify('########'),
            'date_delivrance_piece' => $dateDelivrance->format('Y-m-d'),
            'date_expiration_piece' => $dateExpiration->format('Y-m-d'),
            'cni_recto' => fake()->imageUrl(200, 200, 'people'),
        ];
    }
}
