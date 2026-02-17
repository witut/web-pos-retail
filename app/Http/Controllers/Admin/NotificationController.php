<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notifications
     */
    public function index(Request $request): View|JsonResponse
    {
        $limit = $request->get('limit', 20);

        if ($request->ajax() || $request->get('ajax')) {
            // For AJAX requests (dropdown), return JSON with recent notifications
            $notifications = $this->notificationService->getRecentForUser(auth()->id(), $limit);
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $this->notificationService->getUnreadCount(auth()->id())
            ]);
        }

        // For full page view
        $notifications = $this->notificationService->getAllForUser(auth()->id(), $limit);
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Get unread notification count (AJAX endpoint)
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());

        return response()->json(['count' => $count]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id);

        if (!$success) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->notificationService->markAllAsRead(auth()->id());

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete a notification
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->notificationService->delete($id);

        if (!$success) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json(['message' => 'Notification deleted']);
    }
}
