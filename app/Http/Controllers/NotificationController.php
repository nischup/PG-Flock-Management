<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Get user notifications
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = $this->notificationService->getStats($user);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'stats' => $stats,
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown
     */
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $request->user();

        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json(['success' => true, 'count' => $count]);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, UserNotification $notification): JsonResponse
    {
        $user = $request->user();

        // Ensure user can only delete their own notifications
        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get notification statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->notificationService->getStats($user);

        return response()->json($stats);
    }
}
