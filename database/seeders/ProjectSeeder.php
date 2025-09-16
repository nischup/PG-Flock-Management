<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the company IDs that were created by CompanySeeder
        $provitaChicksId = DB::table('companies')->where('short_name', 'PCL')->value('id');
        $provitaBreedId = DB::table('companies')->where('short_name', 'PBL')->value('id');
        $provitaHatchId = DB::table('companies')->where('short_name', 'PHL')->value('id');

        DB::table('projects')->insert([
            [
                'company_id' => $provitaHatchId,
                'name' => 'PHL-1',
                'location' => 'Dhaka, Bangladesh',
                'contact_person_name' => 'Abdul Karim',
                'contact_person_phone' => '+8801711223344',
                'contact_person_email' => 'karim@provita.com',
                'contact_person_designation' => 'Project Manager',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $provitaHatchId,
                'name' => 'PHL-2',
                'location' => 'Gazipur, Bangladesh',
                'contact_person_name' => 'Sadia Akter',
                'contact_person_phone' => '+8801811556677',
                'contact_person_email' => 'sadia@provita.com',
                'contact_person_designation' => 'Assistant Manager',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $provitaBreedId,
                'name' => 'PBL-1',
                'location' => 'Chattogram, Bangladesh',
                'contact_person_name' => 'Rafiqul Islam',
                'contact_person_phone' => '+8801911998877',
                'contact_person_email' => 'rafiq@kazifarms.com',
                'contact_person_designation' => 'Project Coordinator',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),          
            ],
            [
                'company_id' => $provitaBreedId,
                'name' => 'PBL-2',
                'location' => 'Khulna, Bangladesh',
                'contact_person_name' => 'Mahbub Alam',
                'contact_person_phone' => '+8801711667788',
                'contact_person_email' => 'mahbub@aftabhatchery.com',
                'contact_person_designation' => 'Site Manager',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
