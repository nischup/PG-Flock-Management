<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaccineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get vaccine type IDs
        $liveVaccineId = DB::table('vaccine_types')->where('name', 'Live Vaccine')->value('id');
        $inactivatedVaccineId = DB::table('vaccine_types')->where('name', 'Inactivated Vaccine')->value('id');

        DB::table('vaccines')->insert([
            [
                'vaccine_type_id' => $liveVaccineId,
                'name' => 'Newcastle Disease Vaccine',
                'applicator' => 'Injection',
                'dose' => '0.5 ml per bird',
                'note' => 'Give to chicks at 7 days old',
                'description' => 'Protects against Newcastle disease, a viral infection causing respiratory and neurological issues.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vaccine_type_id' => $inactivatedVaccineId,
                'name' => 'Fowl Pox Vaccine',
                'applicator' => 'Wing Web',
                'dose' => '0.2 ml per bird',
                'note' => 'Use for birds older than 8 weeks',
                'description' => 'Prevents Fowl Pox, causing lesions on skin and comb.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vaccine_type_id' => $inactivatedVaccineId,
                'name' => 'Marek\'s Disease Vaccine',
                'applicator' => 'Injection',
                'dose' => '0.2 ml per bird',
                'note' => 'Administer to day-old chicks',
                'description' => 'Protects against Marek\'s disease, a viral disease causing tumors and paralysis.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
