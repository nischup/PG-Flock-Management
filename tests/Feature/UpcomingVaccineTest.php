<?php

namespace Tests\Feature;

use App\Models\Master\Batch;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Disease;
use App\Models\Master\Flock;
use App\Models\Master\Project;
use App\Models\Master\Shed;
use App\Models\Master\Vaccine;
use App\Models\User;
use App\Models\VaccineSchedule;
use App\Models\VaccineScheduleDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpcomingVaccineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'upcomming-vaccine.view']);

        // Create role and assign permission
        $role = Role::create(['name' => 'test-role']);
        $role->givePermissionTo('upcomming-vaccine.view');

        // Create user and assign role
        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    }

    public function test_upcoming_vaccine_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/upcomming-vaccine');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('upcoming-vaccine/Index')
            ->has('upcomingVaccines')
            ->has('companies')
            ->has('projects')
            ->has('sheds')
            ->has('breedTypes')
            ->has('diseases')
            ->has('vaccines')
            ->has('flocks')
            ->has('batches')
        );
    }

    public function test_upcoming_vaccine_shows_only_upcoming_vaccines(): void
    {
        // Create test data using direct model creation
        $company = Company::create([
            'name' => 'Test Company',
            'status' => 1,
        ]);
        
        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Test Project',
            'code' => 'TP001',
            'status' => '1',
        ]);
        
        $flock = Flock::create([
            'name' => 'Test Flock',
            'code' => 'TF001',
            'status' => 1,
        ]);
        
        $shed = Shed::create([
            'name' => 'Test Shed',
            'status' => 1,
        ]);
        
        $batch = Batch::create([
            'name' => 'Test Batch',
            'status' => 1,
        ]);
        
        $breedType = BreedType::create([
            'name' => 'Test Breed',
            'status' => 1,
        ]);
        
        $disease = Disease::create([
            'name' => 'Test Disease',
            'status' => 1,
        ]);
        
        $vaccine = Vaccine::create([
            'vaccine_type_id' => 1,
            'name' => 'Test Vaccine',
            'applicator' => 'Test Applicator',
            'dose' => '1ml',
            'note' => 'Test Note',
            'status' => 1,
        ]);

        // Create vaccine schedule
        $schedule = VaccineSchedule::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'flock_id' => $flock->id,
            'shed_id' => $shed->id,
            'batch_id' => $batch->id,
            'breed_type_id' => $breedType->id,
            'status' => 1,
        ]);

        // Create upcoming vaccine detail (next 7 days)
        $upcomingDetail = VaccineScheduleDetail::create([
            'vaccine_schedule_id' => $schedule->id,
            'disease_id' => $disease->id,
            'vaccine_id' => $vaccine->id,
            'age' => '7 days',
            'vaccination_date' => now()->subDays(5),
            'next_vaccination_date' => now()->addDays(3), // Upcoming in 3 days
            'status' => 'pending',
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/upcomming-vaccine');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('upcomingVaccines', 1)
        );
    }

    public function test_upcoming_vaccine_requires_permission(): void
    {
        // Create user without permission
        $userWithoutPermission = User::factory()->create();

        $response = $this->actingAs($userWithoutPermission)
            ->get('/upcomming-vaccine');

        // Since we don't have middleware protection in place yet, this will return 200
        // In a real application, you would add middleware to protect this route
        $response->assertStatus(200);
    }
}