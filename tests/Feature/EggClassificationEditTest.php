<?php

namespace Tests\Feature;

use App\Models\Master\EggType;
use App\Models\Production\EggClassification;
use App\Models\Production\EggClassificationRejected;
use App\Models\Production\EggClassificationTechnical;
use App\Models\Shed\BatchAssign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EggClassificationEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected EggClassification $classification;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create test data
        $this->createTestData();
    }

    protected function createTestData(): void
    {
        // Create batch assign
        $batchAssign = BatchAssign::factory()->create();

        // Create egg types
        $rejectedEggType = EggType::factory()->create([
            'name' => 'double_yolk',
            'category' => 1, // rejected
        ]);

        $technicalEggType = EggType::factory()->create([
            'name' => 'floor_egg',
            'category' => 2, // technical
        ]);

        // Create classification
        $this->classification = EggClassification::factory()->create([
            'batchassign_id' => $batchAssign->id,
            'classification_date' => '2024-01-15',
            'total_eggs' => 100,
            'hatching_eggs' => 80,
            'commercial_eggs' => 10,
            'rejected_eggs' => 10,
            'technical_eggs' => 5,
            'created_by' => $this->user->id,
        ]);

        // Create rejected egg record
        EggClassificationRejected::factory()->create([
            'classification_id' => $this->classification->id,
            'egg_type_id' => $rejectedEggType->id,
            'quantity' => 5,
            'note' => 'Test rejected note',
        ]);

        // Create technical egg record
        EggClassificationTechnical::factory()->create([
            'classification_id' => $this->classification->id,
            'egg_type_id' => $technicalEggType->id,
            'quantity' => 3,
            'note' => 'Test technical note',
        ]);
    }

    public function test_edit_page_loads_with_classification_data(): void
    {
        $response = $this->get(route('egg-classification.edit', $this->classification->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('production/egg-classification/Edit')
            ->has('classification')
            ->has('batchAssign')
            ->where('classification.id', $this->classification->id)
            ->where('classification.total_eggs', 100)
            ->where('classification.classification_date', '2024-01-15')
        );
    }

    public function test_edit_page_includes_related_data(): void
    {
        $response = $this->get(route('egg-classification.edit', $this->classification->id));

        $response->assertInertia(fn ($page) => $page->has('classification.batchAssign')
            ->has('classification.rejectedEggs')
            ->has('classification.technicalEggs')
            ->has('classification.rejectedEggs.0.eggType')
            ->has('classification.technicalEggs.0.eggType')
        );
    }

    public function test_edit_page_returns_404_for_nonexistent_classification(): void
    {
        $response = $this->get(route('egg-classification.edit', 99999));

        $response->assertStatus(404);
    }

    public function test_update_classification_successfully(): void
    {
        $updateData = [
            'batchassign_id' => $this->classification->batchassign_id,
            'classification_date' => '2024-01-16',
            'total_egg' => 120,
            'double_yolk' => 8,
            'double_yolk_note' => 'Updated rejected note',
            'floor_egg' => 4,
            'floor_egg_note' => 'Updated technical note',
        ];

        $response = $this->put(route('egg-classification.update', $this->classification->id), $updateData);

        $response->assertRedirect(route('egg-classification.index'));
        $response->assertSessionHas('success', 'Egg Classification updated successfully.');

        // Verify the classification was updated
        $this->classification->refresh();
        $this->assertEquals(120, $this->classification->total_eggs);
        $this->assertEquals('2024-01-16', $this->classification->classification_date);
        $this->assertEquals(112, $this->classification->hatching_eggs); // 120 - 8
        $this->assertEquals(8, $this->classification->rejected_eggs);
        $this->assertEquals(4, $this->classification->technical_eggs);
    }

    public function test_update_validation_prevents_negative_hatching_eggs(): void
    {
        $updateData = [
            'batchassign_id' => $this->classification->batchassign_id,
            'classification_date' => '2024-01-16',
            'total_egg' => 50,
            'double_yolk' => 60, // This would cause negative hatching eggs
            'double_yolk_note' => 'Too many rejected',
        ];

        $response = $this->put(route('egg-classification.update', $this->classification->id), $updateData);

        $response->assertSessionHasErrors(['rejected_total']);
    }

    public function test_update_validation_requires_positive_total_eggs(): void
    {
        $updateData = [
            'batchassign_id' => $this->classification->batchassign_id,
            'classification_date' => '2024-01-16',
            'total_egg' => 0, // Invalid
            'double_yolk' => 5,
        ];

        $response = $this->put(route('egg-classification.update', $this->classification->id), $updateData);

        $response->assertSessionHasErrors(['total_egg']);
    }
}
