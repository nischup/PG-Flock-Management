<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationSoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sound_composable_exists(): void
    {
        $this->assertFileExists(resource_path('js/composables/useNotificationSound.ts'));
    }

    public function test_notification_sound_file_exists(): void
    {
        $this->assertFileExists(public_path('Audio/notification-sound.mp3'));
    }

    public function test_notification_bell_component_has_sound_integration(): void
    {
        $content = file_get_contents(resource_path('js/components/NotificationBell.vue'));
        
        $this->assertStringContainsString('useNotificationSound', $content);
        $this->assertStringContainsString('playSound', $content);
        $this->assertStringContainsString('preloadAudio', $content);
    }

    public function test_realtime_notification_component_has_sound_integration(): void
    {
        $content = file_get_contents(resource_path('js/components/RealtimeNotification.vue'));
        
        $this->assertStringContainsString('useNotificationSound', $content);
        $this->assertStringContainsString('playSound', $content);
    }

    public function test_app_sidebar_header_has_sound_integration(): void
    {
        $content = file_get_contents(resource_path('js/components/AppSidebarHeader.vue'));
        
        $this->assertStringContainsString('useNotificationSound', $content);
        $this->assertStringContainsString('playSound', $content);
    }

    public function test_notification_service_can_send_notifications(): void
    {
        $user = User::factory()->create();
        $notificationService = app(NotificationService::class);

        $notification = $notificationService->send(
            $user,
            'test',
            'Test Notification',
            'This is a test notification',
            [],
            null,
            'bell',
            'normal'
        );

        $this->assertInstanceOf(UserNotification::class, $notification);
        $this->assertEquals('Test Notification', $notification->title);
        $this->assertEquals('This is a test notification', $notification->message);
    }

    public function test_notification_api_endpoints_exist(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        // Test recent notifications endpoint
        $response = $this->getJson('/api/notifications/recent');
        $response->assertStatus(200);
        
        // Test unread count endpoint
        $response = $this->getJson('/api/notifications/unread-count');
        $response->assertStatus(200);
        $response->assertJsonStructure(['count']);
    }
}
