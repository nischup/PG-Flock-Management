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
            ['name' => 'Live Vaccine', 'status' => 1,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Inactivated Vaccine', 'status' => 1,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Combination Vaccine', 'status' => 1,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Recombinant Vaccine', 'status' => 1,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vector Vaccine', 'status' => 1,'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
