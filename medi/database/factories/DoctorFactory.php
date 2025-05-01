<?php

namespace Database\Factories;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
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
            'specialization' => $this->faker->randomElement(['Cardiology', 'Orthopedics', 'Pediatrics', 'General']),
            'email' => $this->faker->unique()->safeEmail,
            'hospital_id' => Hospital::factory(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'phone_number' => $this->faker->phoneNumber,
            'date_of_birth' => $this->faker->date(),
            'image' => 'hospitals/' . fake()->uuid . '.jpg',
        ];
    }
}
