<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {--user-id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test notifications to demonstrate the notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $notificationService = app(NotificationService::class);

        $this->info("Creating test notifications for user: {$user->name}");

        // Test approval notification
        $notificationService->sendApprovalNotification(
            user: $user,
            action: 'pending',
            module: 'ps-receive',
            recordId: 123,
            additionalData: ['batch_id' => 'BATCH-001']
        );

        // Test flock notification
        $notificationService->sendFlockNotification(
            user: $user,
            event: 'mortality_alert',
            message: 'High mortality rate detected in Batch #BATCH-001. Current rate: 5.2%',
            data: ['batch_id' => 'BATCH-001', 'mortality_rate' => 5.2],
            actionUrl: '/batches/BATCH-001'
        );

        // Test vaccine reminder
        $notificationService->sendFlockNotification(
            user: $user,
            event: 'vaccine_due',
            message: 'Vaccine schedule due for Batch #BATCH-002. Next vaccine: Newcastle Disease',
            data: ['batch_id' => 'BATCH-002', 'vaccine' => 'Newcastle Disease'],
            actionUrl: '/vaccine-schedule'
        );

        // Test system notification
        $notificationService->send(
            user: $user,
            type: 'system',
            title: 'System Maintenance Scheduled',
            message: 'Scheduled maintenance will occur on 2024-01-15 from 2:00 AM to 4:00 AM',
            priority: 'normal',
            icon: 'info'
        );

        // Test urgent alert
        $notificationService->send(
            user: $user,
            type: 'alert',
            title: 'Feed Shortage Alert',
            message: 'Feed inventory is running low. Current stock: 15% remaining',
            priority: 'urgent',
            icon: 'alert-circle',
            actionUrl: '/inventory/feed'
        );

        $this->info("✅ Created 5 test notifications!");
        $this->info("Check your notification bell in the header to see them.");

        return 0;
    }
}
