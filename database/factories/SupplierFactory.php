<?php

namespace Database\Factories;

use App\Models\Master\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'supplier_type' => $this->faker->randomElement(['Local', 'Foreign']),
            'address' => $this->faker->address(),
            'origin' => $this->faker->country(),
            'contact_person' => $this->faker->name(),
            'contact_person_email' => $this->faker->safeEmail(),
            'contact_person_mobile' => $this->faker->phoneNumber(),
            'status' => 1,
        ];
    }
}