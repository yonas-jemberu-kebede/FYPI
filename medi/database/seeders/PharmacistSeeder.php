<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pharmacist;
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
