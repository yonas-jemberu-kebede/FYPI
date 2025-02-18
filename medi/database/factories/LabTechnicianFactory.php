<?php

namespace Database\Factories;

use App\Models\DiagnosticCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabTechnician>
 */
class LabTechnicianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'diagnostic_center_id' => DiagnosticCenter::factory(),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'phone_number' => $this->faker->phoneNumber,
        ];
    }
}
