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
        DB::table('vaccines')->insert([
            [
                'vaccine_type_id' => 1,
                'name' => 'Newcastle Disease Vaccine',
                'applicator' => 'Injection',
                'dose' => '0.5 ml per bird',
                'note' => 'Give to chicks at 7 days old',
                'description' => 'Protects against Newcastle disease, a viral infection causing respiratory and neurological issues.',
                'status' => 1,

                'vaccine_type_id' => 1,
                'name' => 'Gumboro Vaccine',
                'applicator' => 'Oral',
                'dose' => '1 ml per bird',
                'note' => 'Administer at 14 days',
                'description' => 'Prevents Infectious Bursal Disease (IBD), affecting the immune system of young birds.',
                'status' => 1,
            ],
            [
                'vaccine_type_id' => 2,
                'name' => 'Fowl Pox Vaccine',
                'applicator' => 'Wing Web',
                'dose' => '0.2 ml per bird',
                'note' => 'Use for birds older than 8 weeks',
                'description' => 'Prevents Fowl Pox, causing lesions on skin and comb.',
                'status' => 1,
            ],
            [
                'vaccine_type_id' => 2,
                'name' => 'Marek\'s Disease Vaccine',
                'applicator' => 'Injection',
                'dose' => '0.2 ml per bird',
                'note' => 'Administer to day-old chicks',
                'description' => 'Protects against Marek’s disease, a viral disease causing tumors and paralysis.',
                'status' => 1,
            ],
        ]);
    }
}
