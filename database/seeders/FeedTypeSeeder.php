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
            ['name' => 'Starter', 'status' => 1],
            ['name' => 'Grower', 'status' => 1],
            ['name' => 'Finisher', 'status' => 1],
            ['name' => 'Layer', 'status' => 1],
            ['name' => 'Breeder', 'status' => 1],
            ['name' => 'Medicated', 'status' => 1],
            ['name' => 'Supplementary', 'status' => 1],
        ]);
    }
}
