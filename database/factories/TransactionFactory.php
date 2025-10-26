<?php

namespace Database\Factories;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'compte_id' => Compte::factory(),
            'type' => $this->faker->randomElement(['depot', 'retrait']),
            'montant' => $this->faker->numberBetween(100, 10000),
            'date_transaction' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'statut' => $this->faker->randomElement(['termine', 'en_cours', 'annule']),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the transaction is a deposit.
     */
    public function depot(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'depot',
        ]);
    }

    /**
     * Indicate that the transaction is a withdrawal.
     */
    public function retrait(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'retrait',
        ]);
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function termine(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'termine',
        ]);
    }
}
