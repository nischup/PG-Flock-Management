<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'HENDRIX GENETICS',
                'supplier_type' => 'Foreign',
                'address' => 'Dhaka, Bangladesh',
                'origin' => 'Bangladesh',
                'contact_person' => 'Abdul Karim',
                'contact_person_email' => 'karim@provita.com',
                'contact_person_mobile' => '+8801711223344',
                'status' => 1,
            ],
            [
                'name' => 'Aftab Medicines Ltd.',
                'supplier_type' => 'Local',
                'address' => 'Gazipur, Bangladesh',
                'origin' => 'Bangladesh',
                'contact_person' => 'Sadia Akter',
                'contact_person_email' => 'sadia@aftabmed.com',
                'contact_person_mobile' => '+8801811556677',
                'status' => 1,
            ],
            [
                'name' => 'Kazi Hatchery Supplies',
                'supplier_type' => 'Local',
                'address' => 'Chattogram, Bangladesh',
                'origin' => 'Bangladesh',
                'contact_person' => 'Rafiqul Islam',
                'contact_person_email' => 'rafiq@kazihatchery.com',
                'contact_person_mobile' => '+8801911998877',
                'status' => 1,
            ],
            [
                'name' => 'Golden Agro Ltd.',
                'supplier_type' => 'Local',
                'address' => 'Rajshahi, Bangladesh',
                'origin' => 'Bangladesh',
                'contact_person' => 'Mahbub Alam',
                'contact_person_email' => 'mahbub@goldenagro.com',
                'contact_person_mobile' => '+8801711667788',
                'status' => 1,
            ],
            [
                'name' => 'Paragon Vet Supplies',
                'supplier_type' => 'Local',
                'address' => 'Narsingdi, Bangladesh',
                'origin' => 'Bangladesh',
                'contact_person' => 'Nazmul Hasan',
                'contact_person_email' => 'nazmul@paragonvet.com',
                'contact_person_mobile' => '+8801711445566',
                'status' => 1,
            ],
        ]);
    }
}
