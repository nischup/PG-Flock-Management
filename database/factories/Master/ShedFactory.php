<?php

namespace Database\Factories\Master;

use App\Models\Master\Shed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Shed>
 */
class ShedFactory extends Factory
{
    protected $model = Shed::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->numerify('Shed-###'),
            'status' => 1,
        ];
    }
}
