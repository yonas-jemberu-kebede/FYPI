<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->Call([
            PatientSeeder::class,
            HospitalSeeder::class,
            DoctorSeeder::class,
            PharmacySeeder::class,
            DiagnosticCenterSeeder::class,
            // LabTechnicianSeeder::class,
            // PharmacistSeeder::class,
            TestPriceSeeder::class,
            MedicationInventorySeeder::class,
            TestSeeder::class,
        ]);
    }
}
