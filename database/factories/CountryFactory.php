<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'iso_code' => fake()->countryCode(),
            'phone_code' => fake()->numberBetween(1, 999),
            'id_format' => 'x-xxxx-xxxx',
            'phone_format' => 'xxxx-xxxx',
            'phone_min_length' => 8,
            'phone_max_length' => 10,
            'id_min_length' => 8,
            'id_max_length' => 10,
        ];
    }
}
