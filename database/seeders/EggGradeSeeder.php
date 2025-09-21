<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EggGradeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('egg_grades')->insert([
            ['name' => 'A', 'type' => 1, 'min_weight' => 66, 'max_weight' => 70, 'status' => 1],
            ['name' => 'B', 'type' => 1, 'min_weight' => 56, 'max_weight' => 65, 'status' => 1],
            ['name' => 'C', 'type' => 1, 'min_weight' => 45, 'max_weight' => 55, 'status' => 1],
            ['name' => 'A', 'type' => 2, 'min_weight' => 66, 'max_weight' => 70, 'status' => 1],
            ['name' => 'B+', 'type' => 2, 'min_weight' => 60, 'max_weight' => 65, 'status' => 1],
            ['name' => 'B', 'type' => 2, 'min_weight' => 55, 'max_weight' => 60, 'status' => 1],
        ]);
    }
}
