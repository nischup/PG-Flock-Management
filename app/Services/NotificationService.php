<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;

class NotificationService
{
    /**
     * Send a notification to a user
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $icon = null,
        string $priority = 'normal',
        ?\DateTime $expiresAt = null
    ): UserNotification {
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'icon' => $icon,
            'priority' => $priority,
            'expires_at' => $expiresAt,
        ]);

        // Broadcast real-time notification
        $this->broadcastNotification($user, $notification);

        Log::info("Notification sent to user {$user->id}: {$title}");

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $icon = null,
        string $priority = 'normal',
        ?\DateTime $expiresAt = null
    ): int {
        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'action_url' => $actionUrl,
                'icon' => $icon,
                'priority' => $priority,
                'expires_at' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        UserNotification::insert($notifications);

        // Broadcast to all users
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->broadcastNotification($user, (object) [
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                    'action_url' => $actionUrl,
                    'icon' => $icon,
                    'priority' => $priority,
                ]);
            }
        }

        Log::info("Bulk notification sent to " . count($userIds) . " users: {$title}");

        return count($notifications);
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole(
        string $role,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $icon = null,
        string $priority = 'normal',
        ?\DateTime $expiresAt = null
    ): int {
        $users = User::role($role)->pluck('id')->toArray();
        
        if (empty($users)) {
            Log::warning("No users found with role: {$role}");
            return 0;
        }

        return $this->sendToUsers($users, $type, $title, $message, $data, $actionUrl, $icon, $priority, $expiresAt);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(UserNotification $notification): bool
    {
        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Delete expired notifications
     */
    public function deleteExpired(): int
    {
        return UserNotification::where('expires_at', '<', now())->delete();
    }

    /**
     * Get notification statistics for a user
     */
    public function getStats(User $user): array
    {
        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => $user->notifications()
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
        ];
    }

    /**
     * Broadcast notification for real-time updates
     */
    private function broadcastNotification(User $user, $notification): void
    {
        try {
            // For now, just log the notification since we're using log broadcaster
            // In production, you would use Pusher, Redis, or another real-time solution
            Log::info("Notification sent to user {$user->id}: " . json_encode([
                'notification' => $notification,
                'timestamp' => now()->toISOString(),
            ]));
            
            // TODO: Implement real-time broadcasting with Pusher/WebSockets
            // Broadcast::toUser($user)->event('notification.received', [
            //     'notification' => $notification,
            //     'timestamp' => now()->toISOString(),
            // ]);
        } catch (\Exception $e) {
            Log::error("Failed to broadcast notification: " . $e->getMessage());
        }
    }

    /**
     * Send approval notification
     */
    public function sendApprovalNotification(
        User $user,
        string $action,
        string $module,
        int $recordId,
        array $additionalData = []
    ): UserNotification {
        $titles = [
            'pending' => 'New Approval Request',
            'approved' => 'Approval Request Approved',
            'rejected' => 'Approval Request Rejected',
            'expired' => 'Approval Request Expired',
        ];

        $icons = [
            'pending' => 'clock',
            'approved' => 'check-circle',
            'rejected' => 'x-circle',
            'expired' => 'alert-circle',
        ];

        $priorities = [
            'pending' => 'high',
            'approved' => 'normal',
            'rejected' => 'high',
            'expired' => 'urgent',
        ];

        return $this->send(
            user: $user,
            type: 'approval',
            title: $titles[$action] ?? 'Approval Update',
            message: "Your {$module} approval request #{$recordId} has been {$action}.",
            data: array_merge([
                'module' => $module,
                'record_id' => $recordId,
                'action' => $action,
            ], $additionalData),
            actionUrl: $this->getApprovalActionUrl($module, $recordId),
            icon: $icons[$action] ?? 'info',
            priority: $priorities[$action] ?? 'normal',
        );
    }

    /**
     * Send flock management notification
     */
    public function sendFlockNotification(
        User $user,
        string $event,
        string $message,
        array $data = [],
        ?string $actionUrl = null
    ): UserNotification {
        $priorities = [
            'mortality_alert' => 'urgent',
            'feed_shortage' => 'high',
            'vaccine_due' => 'normal',
            'transfer_completed' => 'low',
        ];

        return $this->send(
            user: $user,
            type: 'flock',
            title: ucfirst(str_replace('_', ' ', $event)),
            message: $message,
            data: $data,
            actionUrl: $actionUrl,
            icon: 'feather',
            priority: $priorities[$event] ?? 'normal',
        );
    }

    /**
     * Get approval action URL based on module
     */
    private function getApprovalActionUrl(string $module, int $recordId): string
    {
        $routes = [
            'ps-receive' => "ps/ps-receive/{$recordId}",
            'ps-firm-receive' => "ps/ps-firm-receive/{$recordId}",
            'bird-transfer' => "bird-transfer/{$recordId}",
        ];

        return $routes[$module] ?? 'dashboard';
    }
}
