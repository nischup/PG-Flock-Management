<?php

namespace Tests\Feature;

use App\Models\EggGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EggClassificationGradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_egg_classification_grades_page_loads_with_grade_data(): void
    {
        // Create test grades with min_weight and max_weight
        EggGrade::create([
            'name' => 'A',
            'type' => 1, // Commercial
            'min_weight' => 45.00,
            'max_weight' => 55.00,
            'status' => 1,
        ]);

        EggGrade::create([
            'name' => 'B',
            'type' => 1, // Commercial
            'min_weight' => 56.00,
            'max_weight' => 65.00,
            'status' => 1,
        ]);

        // Create a test user
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->actingAs($user);

        // Visit the egg classification grades page
        $response = $this->get(route('egg-classification-grades.index'));

        // Assert the page loads successfully
        $response->assertStatus(200);

        // Assert the page contains the grade data with weight information
        $response->assertInertia(fn ($page) => $page->component('production/egg-classification/Grade')
            ->has('classifications')
            ->has('grades')
            ->where('grades.0.name', 'A')
            ->where('grades.0.min_weight', 45)
            ->where('grades.0.max_weight', 55)
        );
    }

    public function test_grade_data_includes_weight_information(): void
    {
        // Create a commercial grade with weight data
        EggGrade::create([
            'name' => 'B',
            'type' => 1,
            'min_weight' => 56.00,
            'max_weight' => 65.00,
            'status' => 1,
        ]);

        $user = User::factory()->create(['email' => 'test2@example.com']);
        $this->actingAs($user);

        $response = $this->get(route('egg-classification-grades.index'));

        $response->assertStatus(200);

        // Verify the grade data includes weight information
        $response->assertInertia(fn ($page) => $page->has('grades')
            ->where('grades.0.min_weight', 56)
            ->where('grades.0.max_weight', 65)
        );
    }
}
