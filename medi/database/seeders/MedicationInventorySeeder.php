<?php

namespace Database\Seeders;

use App\Models\MedicationInventory;
use Illuminate\Database\Seeder;

class MedicationInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MedicationInventory::factory(2)->create();
    }
}
