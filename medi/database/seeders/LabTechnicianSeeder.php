<?php

namespace Database\Seeders;

use App\Models\LabTechnician;
use Illuminate\Database\Seeder;

class LabTechnicianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LabTechnician::factory(6)->create();
    }
}
