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
            ['name' => 'Newcastle Disease', 'status' => 1,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Avian Influenza (Bird Flu)', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Infectious Bronchitis', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fowl Pox', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marek\'s Disease', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coccidiosis', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gumboro', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mycoplasmosis', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salmonellosis', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Avian Cholera', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
