<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChickTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('chick_types')->insert([
            ['name' => 'Broiler', 'status' => 1],
            ['name' => 'Layer', 'status' => 1],
            ['name' => 'Breeder', 'status' => 1],
            ['name' => 'Dual Purpose', 'status' => 1],
        ]);
    }
}
