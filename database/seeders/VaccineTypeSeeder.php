<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaccineTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vaccine_types')->insert([
            ['name' => 'Live Vaccine', 'status' => 1],
            ['name' => 'Inactivated Vaccine', 'status' => 1],
            ['name' => 'Combination Vaccine', 'status' => 1],
            ['name' => 'Recombinant Vaccine', 'status' => 1],
            ['name' => 'Vector Vaccine', 'status' => 1],
        ]);
    }
}
