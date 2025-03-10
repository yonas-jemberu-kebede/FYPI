<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $authenticatedUser = $request->user();
        if (! $authenticatedUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $associatedEntity = $authenticatedUser->associatedEntity;
        if (! $associatedEntity) {
            return response()->json(['error' => 'No associated entity found'], 403); // Or handle differently
        }

        $notifications = Notification::where('notifiable_type', get_class($associatedEntity))
            ->where('notifiable_id', $associatedEntity->id)
            ->whereNull('read_at')
            ->get();

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $authenticatedUser = $request->user();
        if (! $authenticatedUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $associatedEntity = $authenticatedUser->associatedEntity;
        if (! $associatedEntity) {
            return response()->json(['error' => 'No associated entity found'], 403); // Or handle differently
        }

        if ($notification->notifiable_type !== get_class($associatedEntity) || $notification->notifiable_id !== $associatedEntity->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read']);
    }
}
