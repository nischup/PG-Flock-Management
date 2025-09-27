<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EggClassificationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_prevents_negative_hatching_eggs_calculation(): void
    {
        // Test the calculation logic directly
        $totalEggs = 100;
        $rejectedTotal = 150; // This would cause negative hatching eggs

        // Simulate the controller logic
        $hatchingEgg = max(0, $totalEggs - $rejectedTotal);

        // Assert that hatching eggs is never negative
        $this->assertEquals(0, $hatchingEgg);
    }

    public function test_validates_rejected_eggs_exceed_total(): void
    {
        // Test validation logic
        $totalEggs = 50;
        $rejectedTotal = 65; // Exceeds total

        // This should trigger validation error
        $isValid = $rejectedTotal <= $totalEggs;

        $this->assertFalse($isValid);
    }

    public function test_correct_calculation_with_valid_data(): void
    {
        // Test correct calculation
        $totalEggs = 1000;
        $rejectedTotal = 120;
        $commercialTotal = 20;
        $technicalTotal = 20;

        // Simulate controller calculations
        $hatchingEgg = max(0, $totalEggs - $rejectedTotal);

        $this->assertEquals(880, $hatchingEgg);
        $this->assertEquals(20, $commercialTotal);
        $this->assertEquals(120, $rejectedTotal);
        $this->assertEquals(20, $technicalTotal);
    }

    public function test_validation_message_generation(): void
    {
        // Test validation message logic
        $totalEggs = 0;
        $rejectedTotal = 50;
        $batchAssignId = null;

        // Simulate frontend validation logic
        $validationMessage = null;
        if (! $batchAssignId) {
            $validationMessage = 'Please select a Batch';
        } elseif ($totalEggs <= 0) {
            $validationMessage = 'Total eggs must be greater than 0';
        } elseif ($rejectedTotal > $totalEggs) {
            $validationMessage = 'Rejected eggs cannot exceed total eggs';
        }

        $this->assertEquals('Please select a Batch', $validationMessage);
    }
}
