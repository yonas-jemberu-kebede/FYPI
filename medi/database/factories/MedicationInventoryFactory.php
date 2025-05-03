<?php

namespace Database\Factories;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicationInventory>
 */
class MedicationInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory(), // Creates a new Hospital or references an existing one
            'medication_name' => $this->faker->randomElement([
                'Paracetamol',
                'Ibuprofen',
                'Amoxicillin',
                'Metformin',
                'Aspirin',
                'Lisinopril',
                'Atorvastatin',
            ]), // Common medication names
            'price_per_unit' => $this->faker->randomFloat(2, 0.5, 50), // e.g., 5.99
            'quantity_available' => $this->faker->numberBetween(10, 500), // e.g., 100
        ];
    }
}
