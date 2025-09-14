<?php

namespace Database\Factories;

use App\Models\BirdTransfer\BirdTransfer;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Shed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BirdTransfer\BirdTransfer>
 */
class BirdTransferFactory extends Factory
{
    protected $model = BirdTransfer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_assign_id' => 1,
            'job_no' => $this->faker->unique()->numerify('JOB-###'),
            'transaction_no' => $this->faker->unique()->numerify('TXN-###'),
            'flock_no' => $this->faker->numberBetween(1, 999),
            'flock_id' => Flock::factory(),
            'from_company_id' => Company::factory(),
            'to_company_id' => Company::factory(),
            'from_shed_id' => Shed::factory(),
            'to_shed_id' => null,
            'transfer_date' => $this->faker->date(),
            'transfer_female_qty' => $this->faker->numberBetween(100, 5000),
            'transfer_male_qty' => $this->faker->numberBetween(50, 500),
            'transfer_total_qty' => function (array $attributes) {
                return $attributes['transfer_female_qty'] + $attributes['transfer_male_qty'];
            },
            'medical_female_qty' => $this->faker->numberBetween(0, 10),
            'medical_male_qty' => $this->faker->numberBetween(0, 10),
            'medical_total_qty' => function (array $attributes) {
                return $attributes['medical_female_qty'] + $attributes['medical_male_qty'];
            },
            'deviation_female_qty' => 0,
            'deviation_male_qty' => 0,
            'deviation_total_qty' => 0,
            'created_by' => 1,
            'updated_by' => null,
            'status' => 1,
        ];
    }
}
