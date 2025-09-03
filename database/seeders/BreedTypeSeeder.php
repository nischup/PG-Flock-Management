<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreedTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
             DB::table('breed_types')->insert([
            ['name' => 'Ross', 'status' => '1'],
            ['name' => 'Rainbow', 'status' => '1'],
            ['name' => 'Ruby', 'status' => '1'],
        ]);
    }
}
