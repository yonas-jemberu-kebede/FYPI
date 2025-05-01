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
            'name' => fake()->company . ' Hospital',
            'email' => fake()->unique()->safeEmail,
            'phone_number' => fake()->phoneNumber,
            'address' => fake()->streetAddress,
            'account' => encrypt(fake()->bankAccountNumber),
            'city' => fake()->city,
            'country' => fake()->country,
            'latitude' => fake()->latitude(-90, 90),
            'longitude' => fake()->longitude(-180, 180),
            'hospital_type' => fake()->randomElement(['General', 'Specialized', 'Clinic', 'Teaching']),
            'icu_capacity' => fake()->numberBetween(0, 100),
            'established_year' => fake()->year,
            'operating_hours' => fake()->randomElement(['24/7', '9 AM - 5 PM', '8 AM - 8 PM']),
            'image' => 'hospitals/' . fake()->uuid . '.jpg',
        ];
    }
}
