<?php

namespace Database\Factories;

use App\Models\Master\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Flock>
 */
class FlockFactory extends Factory
{
    protected $model = Flock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('FLK-###'),
            'name' => $this->faker->unique()->numerify('###'),
            'parent_flock_id' => null,
            'status' => 1,
        ];
    }
}
