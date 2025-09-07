<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          DB::table('units')->insert([
            ['name' => 'KG', 'status' => '1',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MT', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tonne', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gm', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Litre', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ML', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Box', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dozen', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bottle', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Packet', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PC', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gram', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liter', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liter', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ML', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Box', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dozen', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bottle', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Packet', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
