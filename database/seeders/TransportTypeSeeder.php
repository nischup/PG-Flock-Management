<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transport_types')->insert([
            ['name' => 'Truck', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Van', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pickup', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Motorbike', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Boat', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Air Cargo', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
