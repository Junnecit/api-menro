<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppNotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => AppNotificationResource::collection($notifications),
            'unread_count' => AppNotification::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => AppNotification::query()
                    ->where('user_id', $request->user()->id)
                    ->whereNull('read_at')
                    ->count(),
            ],
        ]);
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return response()->json([
            'success' => true,
            'data' => new AppNotificationResource($notification),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function registerPushToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'expo_push_token' => ['required', 'string', 'max:255'],
            'push_enabled' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $user->expo_push_token = $data['expo_push_token'];
        if (array_key_exists('push_enabled', $data)) {
            $user->push_enabled = (bool) $data['push_enabled'];
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Push token registered.',
            'data' => [
                'push_enabled' => $user->push_enabled,
            ],
        ]);
    }

    public function updatePushPreference(Request $request): JsonResponse
    {
        $data = $request->validate([
            'push_enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $user->push_enabled = $data['push_enabled'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Push preference updated.',
            'data' => [
                'push_enabled' => $user->push_enabled,
            ],
        ]);
    }
}
