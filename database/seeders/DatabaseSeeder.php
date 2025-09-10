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
            PermissionSeeder::class,
            BatchSeeder::class,
            BreedTypeSeeder::class,
            ChickTypeSeeder::class,
            CompanySeeder::class,
            CountrySeeder::class,
            DiseaseSeeder::class,
            FeedSeeder::class,
            FeedTypeSeeder::class,
            LevelSeeder::class,
            MedicineSeeder::class,
            ProjectSeeder::class,
            ShedSeeder::class,
            SupplierSeeder::class,
            TransportTypeSeeder::class,
            UnitSeeder::class,
            VaccineTypeSeeder::class,
            VaccineSeeder::class,
            EggTypesSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Provita',
            'email' => 'provita@mail.com',
        ]);
    }
}
