<?php

namespace Tests\Feature;

use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\Batch;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Shed\BatchAssign;
use App\Models\Shed\ShedReceive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirdTransferListTest extends TestCase
{
    use RefreshDatabase;

    public function test_bird_transfer_list_loads_with_shed_and_batch_names(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create test data
        $company = Company::factory()->create();
        $flock = Flock::factory()->create();
        $shed = Shed::factory()->create();
        $batch = Batch::factory()->create();

        // Create shed receive first
        $shedReceive = ShedReceive::create([
            'job_no' => 'TEST-JOB-001',
            'transaction_no' => 'TEST-TXN-001',
            'flock_no' => 1,
            'flock_id' => $flock->id,
            'company_id' => $company->id,
            'shed_id' => $shed->id,
            'receive_date' => now(),
            'receive_female_qty' => 100,
            'receive_male_qty' => 100,
            'receive_total_qty' => 200,
            'created_by' => $user->id,
        ]);

        // Create batch assign
        $batchAssign = BatchAssign::factory()->create([
            'shed_receive_id' => $shedReceive->id,
            'flock_id' => $flock->id,
            'company_id' => $company->id,
            'shed_id' => $shed->id,
            'batch_no' => $batch->id,
        ]);

        // Create bird transfer
        $birdTransfer = BirdTransfer::factory()->create([
            'flock_id' => $flock->id,
            'from_company_id' => $company->id,
            'to_company_id' => $company->id,
            'batch_assign_id' => $batchAssign->id,
        ]);

        // Act as the user and visit the bird transfer list page
        $response = $this->actingAs($user)->get('/bird-transfer');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that the response contains the bird transfer data
        $response->assertInertia(fn ($page) => $page->component('transfer/bird-transfer/List')
            ->has('birdTransfers.data', 1)
            ->where('birdTransfers.data.0.id', $birdTransfer->id)
        );
    }
}
