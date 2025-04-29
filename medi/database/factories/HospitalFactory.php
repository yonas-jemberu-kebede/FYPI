<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hospital>
 */
class HospitalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().'Hospital',
            'email' => fake()->unique()->email(),
            'address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
            'account' => 'CHASECK_TEST - 4 M3phlAp4st05LGUAjM3c3oBonocWghg',
            'city' => fake()->city,
            'country' => fake()->country,
            'latitude' => fake()->latitude,
            'longitude' => fake()->longitude,

            'hospital_type' => fake()->randomElement(['Public', 'Private', 'Specialized', 'Teaching']),
            'established_year' => fake()->year,
            'operating_hours' => fake()->randomElement(['24/7', '8AM-8PM', '9AM-5PM']),
            'icu_capacity' => fake()->numberBetween(5, 50),

        ];
    }
}
