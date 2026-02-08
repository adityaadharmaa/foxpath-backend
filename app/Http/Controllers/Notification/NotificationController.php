<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notifService
    ) {}

    public function index(Request $request)
    {
        return $this->notifService->getUserNotifications($request->user(), $request->query('per_page', 10, $request->query('type')));
    }

    public function unreadCount(Request $request)
    {
        return $this->notifService->getUnreadCount($request->user());
    }

    public function markAsRead(Request $request)
    {
        $success = $this->notifService->markAsRead(
            $request->user(),
            $request->input('id')
        );

        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found or access denied.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => $request->input('id') ? 'Notification marked as read.' : 'All notification mark as read.'
        ], 200);
    }

    public function clearRead(Request $request)
    {
        $this->notifService->clearReadNotifications($request->user());
        return response()->json([
            'status' => 'success',
            'message' => 'Old notifications has been deleted.'
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $success = $this->notifService->deleteNotification($request->user(), $id);

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted.'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Notification not found.'
        ], 404);
    }
}
