<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $events = ['created', 'updated', 'deleted', 'viewed', 'login', 'logout'];
        $modelTypes = [
            'App\\Models\\User',
            'App\\Models\\Master\\Company',
            'App\\Models\\Master\\Shed',
            'App\\Models\\DailyOperation\\DailyOperation',
        ];

        return [
            'event' => $this->faker->randomElement($events),
            'auditable_type' => $this->faker->randomElement($modelTypes),
            'auditable_id' => $this->faker->numberBetween(1, 100),
            'old_values' => $this->faker->optional(0.3)->randomElements([
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'status' => $this->faker->randomElement([0, 1]),
            ]),
            'new_values' => $this->faker->optional(0.7)->randomElements([
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'status' => $this->faker->randomElement([0, 1]),
            ]),
            'url' => $this->faker->url(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'tags' => $this->faker->optional(0.5)->randomElement(['authentication', 'data_management', 'system']),
            'user_id' => User::factory(),
        ];
    }
}
