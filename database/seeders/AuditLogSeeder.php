<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        // Create some sample audit logs
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');

            return;
        }

        $events = ['created', 'updated', 'deleted', 'viewed', 'login', 'logout'];
        $modelTypes = [
            'App\\Models\\User',
            'App\\Models\\Master\\Company',
            'App\\Models\\Master\\Shed',
            'App\\Models\\DailyOperation\\DailyOperation',
        ];

        for ($i = 0; $i < 50; $i++) {
            AuditLog::create([
                'event' => fake()->randomElement($events),
                'auditable_type' => fake()->randomElement($modelTypes),
                'auditable_id' => fake()->numberBetween(1, 10),
                'old_values' => fake()->optional(0.3)->randomElements([
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'status' => fake()->randomElement(['active', 'inactive']),
                ]),
                'new_values' => fake()->optional(0.7)->randomElements([
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'status' => fake()->randomElement(['active', 'inactive']),
                ]),
                'url' => fake()->url(),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'tags' => fake()->optional(0.5)->randomElement(['authentication', 'data_management', 'system']),
                'user_id' => $users->random()->id,
            ]);
        }
    }
}
