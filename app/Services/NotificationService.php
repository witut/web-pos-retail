<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification
     */
    public function create(array $data): Notification
    {
        return Notification::create([
            'user_id' => $data['user_id'] ?? null,
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ?? [],
        ]);
    }

    /**
     * Create notification for all admins (broadcast)
     */
    public function createForAllAdmins(string $type, string $title, string $message, array $data = []): Notification
    {
        return $this->create([
            'user_id' => null, // null = broadcast to all admins
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create notification for specific user
     */
    public function createForUser(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        return $this->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id'); // Include broadcast notifications
            })
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentForUser(int $userId, int $limit = 10): Collection
    {
        return Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id'); // Include broadcast notifications
            })
            ->recent($limit)
            ->get();
    }

    /**
     * Get all notifications for user with pagination
     */
    public function getAllForUser(int $userId, int $perPage = 20)
    {
        return Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): bool
    {
        return Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->unread()
            ->update(['read_at' => now()]) > 0;
    }

    /**
     * Delete old notifications
     */
    public function deleteOld(int $days = 30): int
    {
        return Notification::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Delete notification
     */
    public function delete(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }
}
