<?php

namespace Database\Factories\Shed;

use App\Models\Shed\BatchAssign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shed\BatchAssign>
 */
class BatchAssignFactory extends Factory
{
    protected $model = BatchAssign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shed_receive_id' => 1, // Default value for testing
            'job_no' => $this->faker->unique()->numerify('JOB-###'),
            'transaction_no' => $this->faker->unique()->numerify('TXN-###'),
            'flock_no' => $this->faker->numberBetween(1, 1000),
            'flock_id' => 1, // Default value for testing
            'company_id' => 1, // Default value for testing
            'project_id' => 1, // Default value for testing
            'shed_id' => 1, // Default value for testing
            'level' => $this->faker->numberBetween(1, 10),
            'batch_no' => $this->faker->numberBetween(1, 100),
            'batch_female_qty' => $this->faker->numberBetween(100, 1000),
            'batch_male_qty' => $this->faker->numberBetween(100, 1000),
            'batch_total_qty' => $this->faker->numberBetween(200, 2000),
            'batch_received_female_qty' => $this->faker->numberBetween(100, 1000),
            'batch_received_male_qty' => $this->faker->numberBetween(100, 1000),
            'batch_received_total_qty' => $this->faker->numberBetween(200, 2000),
            'batch_female_mortality' => $this->faker->numberBetween(0, 50),
            'batch_male_mortality' => $this->faker->numberBetween(0, 50),
            'batch_total_mortality' => $this->faker->numberBetween(0, 100),
            'batch_excess_male' => $this->faker->numberBetween(0, 20),
            'batch_excess_female' => $this->faker->numberBetween(0, 20),
            'batch_sortage_male' => $this->faker->numberBetween(0, 20),
            'batch_sortage_female' => $this->faker->numberBetween(0, 20),
            'percentage' => $this->faker->numberBetween(80, 100),
            'stage' => $this->faker->numberBetween(1, 5),
            'created_by' => 1, // Default value for testing
        ];
    }
}
