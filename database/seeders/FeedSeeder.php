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
        // Get feed type IDs
        $starterId = DB::table('feed_types')->where('name', 'Starter')->value('id');
        $growerId = DB::table('feed_types')->where('name', 'Grower')->value('id');
        $finisherId = DB::table('feed_types')->where('name', 'Finisher')->value('id');
        $layerId = DB::table('feed_types')->where('name', 'Layer')->value('id');
        $breederId = DB::table('feed_types')->where('name', 'Breeder')->value('id');
        $medicatedId = DB::table('feed_types')->where('name', 'Medicated')->value('id');
        $supplementaryId = DB::table('feed_types')->where('name', 'Supplementary')->value('id');

        DB::table('feeds')->insert([
            ['feed_type_id' => $starterId, 'feed_name' => 'Starter Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $growerId, 'feed_name' => 'Grower Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $finisherId, 'feed_name' => 'Finisher Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $layerId, 'feed_name' => 'Layer Mash', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $breederId, 'feed_name' => 'Breeder Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $medicatedId, 'feed_name' => 'Medicated Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $finisherId, 'feed_name' => 'Pellet Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $growerId, 'feed_name' => 'Mash Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $finisherId, 'feed_name' => 'Crumbled Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['feed_type_id' => $supplementaryId, 'feed_name' => 'Supplementary Feed', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
