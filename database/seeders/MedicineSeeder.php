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
            ['name' => 'Amoxicillin', 'status' => 1],
            ['name' => 'Tylosin', 'status' => 1],
            ['name' => 'Doxycycline', 'status' => 1],
            ['name' => 'Ivermectin', 'status' => 1],
            ['name' => 'Vitamin Supplement', 'status' => 1],
            ['name' => 'Coccidiostat', 'status' => 1],
            ['name' => 'Probiotic', 'status' => 1],
            ['name' => 'Multivitamin', 'status' => 1],
            ['name' => 'Oxytetracycline', 'status' => 1],
            ['name' => 'Enrofloxacin', 'status' => 1],
        ]);
    }
}
