<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('diseases')->insert([
            ['name' => 'Newcastle Disease', 'status' => 1],
            ['name' => 'Avian Influenza (Bird Flu)', 'status' => 1],
            ['name' => 'Infectious Bronchitis', 'status' => 1],
            ['name' => 'Fowl Pox', 'status' => 1],
            ['name' => 'Marek\'s Disease', 'status' => 1],
            ['name' => 'Coccidiosis', 'status' => 1],
            ['name' => 'Infectious Bursal Disease (Gumboro)', 'status' => 1],
            ['name' => 'Mycoplasmosis', 'status' => 1],
            ['name' => 'Salmonellosis', 'status' => 1],
            ['name' => 'Avian Cholera', 'status' => 1],
        ]);
    }
}
