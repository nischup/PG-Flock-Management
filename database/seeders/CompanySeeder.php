<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert([
            [
                'name' => 'Provita Chicks Limited',
                'company_type' => 'Poultry Breeding',
                'location' => 'Dhaka, Bangladesh',
                'contact_person_name' => 'Abdul Karim',
                'contact_person_phone' => '+8801711223344',
                'contact_person_email' => 'karim@provita.com',
                'contact_person_designation' => 'General Manager',
                'status' => 1,
            ],
            [
                'name' => 'Provita Breed Limited',
                'company_type' => 'Poultry Breeding',
                'location' => 'Gazipur, Bangladesh',
                'contact_person_name' => 'Sadia Akter',
                'contact_person_phone' => '+8801811556677',
                'contact_person_email' => 'sadia@provita.com',
                'contact_person_designation' => 'Managing Director',
                'status' => 1,
            ],
        ]);
    }
}
