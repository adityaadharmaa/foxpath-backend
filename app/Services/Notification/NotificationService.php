<?php 

namespace App\Services\Notification;

use App\Models\User;

class NotificationService
{
    public function getUserNotifications(User $user, int $perPage = 10 )
    {
        $notifications = $user->notifications()
                        ->latest()
                        ->paginate($perPage);

        $notifications->through(function ($notif){
            return [
                'id' => $notif->id,
                'type' => $this->simplifyType($notif->data['type'] ?? 'info'),
                'message' => $notif->data['message'] ?? '',
                'action_url' => $notif->data['action_url'] ?? null,
                'is_read' => !is_null($notif->read_at),
                'created_at_human' => $notif->created_at->diffForHumans(),
                'created_at_date' => $notif->created_at->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Notification retrieved successfully.',
            'data' => $notifications
        ], 200);
    }

    public function getUnreadCount(User $user)
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, ?string $notificationId = null)
    {
        if($notificationId)
        {
            $notification = $user->notifications()
                ->where('id', $notificationId)
                ->first();

            if($notification)
            {
                $notification->markAsRead();
                return true;
            }

            return false;
        }

        $user->unreadNotifications->markAsRead();
        return true;
    }

    public function deleteNotification(User $user, string $notificationId)
    {
        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->first();
        
            if($notification) {
                $notification->delete();
                return true;
            }

            return false;
    }

    private function simplifyType(string $type)
    {
        return $type;
    }
}