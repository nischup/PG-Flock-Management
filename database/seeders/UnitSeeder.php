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
            ['name' => 'KG', 'status' => '1'],
            ['name' => 'PC', 'status' => '1'],
            ['name' => 'Gram', 'status' => '1'],
            ['name' => 'Liter', 'status' => '1'],
            ['name' => 'Liter', 'status' => '1'],
            ['name' => 'ML', 'status' => '1'],
            ['name' => 'Box', 'status' => '1'],
            ['name' => 'Dozen', 'status' => '1'],
            ['name' => 'Bottle', 'status' => '1'],
            ['name' => 'Packet', 'status' => '1'],
        ]);
    }
}
