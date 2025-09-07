<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('feed_types')->insert([
            ['name' => 'Starter', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grower', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finisher', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Layer', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Breeder', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medicated', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supplementary', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
