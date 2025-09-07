<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sheds')->insert([
            ['name' => 'Shed-1', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-2', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-3', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-4', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-5', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-6', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-7', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-8', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-9', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shed-10', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
