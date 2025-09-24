<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotification>
 */
class UserNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['flock', 'approval', 'system', 'alert']),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(2),
            'data' => [],
            'action_url' => $this->faker->optional()->url(),
            'icon' => $this->faker->randomElement(['bell', 'clock', 'check-circle', 'x-circle', 'alert-circle', 'feather']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
            'expires_at' => $this->faker->optional(0.2)->dateTimeBetween('now', '+1 month'),
        ];
    }
}
