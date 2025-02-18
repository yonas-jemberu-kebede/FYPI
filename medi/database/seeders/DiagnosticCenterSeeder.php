<?php

namespace Database\Seeders;

use App\Models\DiagnosticCenter;
use Illuminate\Database\Seeder;

class DiagnosticCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiagnosticCenter::factory(2)->create();
    }
}
