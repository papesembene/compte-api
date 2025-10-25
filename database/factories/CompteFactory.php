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
            // numero_compte et solde sont auto-générés par le modèle
            'type_compte' => $this->faker->randomElement(['courant', 'epargne']),
            'statut' => 'debloque', // Statut débloqué par défaut pour les tests
            'client_id' => Client::factory(), // Associe à un client existant
        ];
    }
}
