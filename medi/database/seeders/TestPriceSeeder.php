<?php

namespace Database\Seeders;

use App\Models\TestPrice;
use Illuminate\Database\Seeder;

class TestPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestPrice::factory(7)->create();
    }
}
