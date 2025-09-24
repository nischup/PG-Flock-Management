<?php

namespace Database\Factories\Master;

use App\Models\Master\Batch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'status' => fake()->boolean(),
        ];
    }
}
