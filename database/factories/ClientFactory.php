<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulaire' => $this->faker->name(),
            'nci' => $this->faker->unique()->numerify('#############'),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => '+221' . $this->faker->randomElement([70, 75, 76, 77, 78]) . $this->faker->numberBetween(1000000, 9999999),
            'adresse' => $this->faker->address() . ', Senegal',
            'password' => bcrypt('password'),
        ];
    }
}
