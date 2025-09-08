<?php

namespace Database\Seeders;

use App\Models\Master\Batch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batches = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        foreach ($batches as $batch) {
            Batch::create([
                'name' => "Batch {$batch}",
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
