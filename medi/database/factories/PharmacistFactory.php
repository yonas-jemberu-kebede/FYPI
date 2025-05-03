<?php

namespace Database\Factories;

use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pharmacist>
 */
class PharmacistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'email' => fake()->unique()->safeEmail,
            'gender' => fake()->randomElement(['Male', 'Female']),
            'phone_number' => fake()->phoneNumber,
            'pharmacy_id' => Pharmacy::factory(),
            'shift_day' => fake()->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            'shift_start' => fake()->time('H:i:s', '08:00:00'),
            'shift_end' => fake()->time('H:i:s', '17:00:00'),
        ];
    }
}
