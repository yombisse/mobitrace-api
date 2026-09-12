<?php

namespace Database\Factories;

use App\Models\Reseau;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reseau>
 */
class ReseauFactory extends Factory
{
    protected $model = Reseau::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->company(),
            'code' => fake()->unique()->bothify('??'),
            'logo' => null,
        ];
    }
}
