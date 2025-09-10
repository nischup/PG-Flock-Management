<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\EggType;

class EggTypesSeeder extends Seeder
{
    public function run()
    {
        $eggTypes = [
            // Technical eggs (category = 1)
            ['name' => 'floor_egg', 'category' => 2],
            ['name' => 'thin_egg', 'category' => 2],
            ['name' => 'misshape_egg', 'category' => 2],
            ['name' => 'white_egg', 'category' => 2],
            ['name' => 'dirty_egg', 'category' => 2],

            // Rejected eggs (category = 2)
            ['name' => 'double_yolk', 'category' => 1],
            ['name' => 'double_yolk_broken', 'category' => 1],
            ['name' => 'commercial', 'category' => 1],
            ['name' => 'commercial_broken', 'category' => 1],
            ['name' => 'liquid', 'category' => 1],
            ['name' => 'damage', 'category' => 1],
        ];

        foreach ($eggTypes as $type) {
            EggType::updateOrCreate(
                ['name' => $type['name']], // Avoid duplicates
                ['category' => $type['category'], 'status' => 1]
            );
        }
    }
}
