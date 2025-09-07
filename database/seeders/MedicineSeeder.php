<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('medicines')->insert([
            ['name' => 'Amoxicillin', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tylosin', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Doxycycline', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ivermectin', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vitamin Supplement', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coccidiostat', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Probiotic', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Multivitamin', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Oxytetracycline', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Enrofloxacin', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
