<?php

namespace Database\Seeders;

use App\Models\Pharmacist;
use Illuminate\Database\Seeder;

class PharmacistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pharmacist::factory(5)->create();
    }
}
