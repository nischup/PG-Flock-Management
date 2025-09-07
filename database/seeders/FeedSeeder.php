<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('feeds')->insert([
            ['feed_type_id' => 1, 'feed_name' => 'Starter Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 2, 'feed_name' => 'Grower Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 3, 'feed_name' => 'Finisher Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 4, 'feed_name' => 'Layer Mash', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 5, 'feed_name' => 'Breeder Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 6, 'feed_name' => 'Medicated Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 3, 'feed_name' => 'Pellet Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 2, 'feed_name' => 'Mash Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 3, 'feed_name' => 'Crumbled Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => 7, 'feed_name' => 'Supplementary Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
