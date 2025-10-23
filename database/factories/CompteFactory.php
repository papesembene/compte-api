<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'numero_compte' => $this->faker->unique()->numerify('1#########'), // 10 digits starting with 1-9
            'solde' => $this->faker->randomFloat(2, 0, 1000000), // Solde entre 0 et 1M
            'type_compte' => $this->faker->randomElement(['courant', 'epargne']),
            'client_id' => Client::factory(), // Associe à un client existant
        ];
    }
}
