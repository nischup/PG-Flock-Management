<?php

namespace Database\Factories;

use App\Models\Master\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'short_name' => $this->faker->lexify('???'),
            'company_type' => $this->faker->randomElement(['Breeder', 'Hatchery', 'Farm']),
            'location' => $this->faker->city(),
            'contact_person_name' => $this->faker->name(),
            'contact_person_phone' => $this->faker->phoneNumber(),
            'contact_person_email' => $this->faker->email(),
            'contact_person_designation' => $this->faker->jobTitle(),
            'status' => 1,
        ];
    }
}
