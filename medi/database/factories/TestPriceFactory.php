<?php

namespace Database\Factories;

use App\Models\DiagnosticCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestPrice>
 */
class TestPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_name' => $this->faker->words(2, true), // e.g., "Blood Test"
            'price' => $this->faker->randomFloat(2, 10, 500), // e.g., 150.00
            'diagnostic_center_id' => DiagnosticCenter::factory(), // Creates a new DiagnosticCenter or references an existing one

        ];
    }
}
