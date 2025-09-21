<?php

namespace Database\Seeders;

use App\Models\Master\EggType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // Create companies and sheds first
            CompanySeeder::class,
            ShedSeeder::class,
            // Then create permissions and users
            PermissionSeeder::class,
            // Then create other data
            BatchSeeder::class,
            BreedTypeSeeder::class,
            ChickTypeSeeder::class,
            CountrySeeder::class,
            DiseaseSeeder::class,
            FeedTypeSeeder::class,
            FeedSeeder::class,
            LevelSeeder::class,
            MedicineSeeder::class,
            ProjectSeeder::class,
            SupplierSeeder::class,
            TransportTypeSeeder::class,
            UnitSeeder::class,
            VaccineTypeSeeder::class,
            VaccineSeeder::class,
            EggTypesSeeder::class,
            EggGradeSeeder::class,
            ApprovalMatrixSeeder::class
        ]);

        // Create user only if it doesn't exist
        if (!User::where('email', 'provita@mail.com')->exists()) {
            User::factory()->create([
                'name' => 'Provita',
                'email' => 'provita@mail.com',
            ]);
        }
    }
}
