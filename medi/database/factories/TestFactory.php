<?php

namespace Database\Factories;

use App\Models\DiagnosticCenter;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Test>
 */
class TestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $testNames = [
            'Blood Test',
            'X-Ray',
            'MRI Scan',
            'Urine Analysis',
            'ECG',
            'Ultrasound',
            'CT Scan',
        ];

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'hospital_id' => Hospital::factory(),
            'diagnostic_center_id' => DiagnosticCenter::Factory(), // 30% chance of null
            'total_amount' => $this->faker->randomFloat(2, 50, 1000), // e.g., 150.00
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'test_requests' => json_encode([
                [
                    'name' => $this->faker->randomElement($testNames),
                    'code' => 'TEST'.$this->faker->unique()->numberBetween(100, 999),
                ],
                [
                    'name' => $this->faker->randomElement($testNames),
                    'code' => 'TEST'.$this->faker->unique()->numberBetween(100, 999),
                ],
            ]),

        ];
    }
}
