<?php

namespace Database\Factories;

use App\Models\Master\BreedType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\BreedType>
 */
class BreedTypeFactory extends Factory
{
    protected $model = BreedType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'status' => 1,
        ];
    }
}