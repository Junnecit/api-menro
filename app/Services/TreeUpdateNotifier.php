<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TreeUpdateNotifier
{
    /**
     * Notify the planter who recorded the tree when a monitor/admin edits it.
     */
    public function notifyRecorder(Tree $tree, User $actor): void
    {
        $recorderId = $tree->recorded_by_id;
        if (! $recorderId || $recorderId === $actor->id) {
            return;
        }

        $recorder = User::query()->find($recorderId);
        if (! $recorder) {
            return;
        }

        $species = $tree->species ?: 'Tree';
        $statusValue = $tree->status instanceof \BackedEnum ? $tree->status->value : (string) ($tree->status ?: 'updated');
        $title = 'Your tree was updated';
        $body = sprintf('%s marked "%s" as %s.', $actor->name, $species, str_replace('_', ' ', $statusValue));

        $notification = AppNotification::create([
            'user_id' => $recorder->id,
            'type' => 'tree_updated',
            'title' => $title,
            'body' => $body,
            'data' => [
                'tree_id' => $tree->id,
                'status' => $tree->status,
                'updated_by_id' => $actor->id,
            ],
        ]);

        $this->sendExpoPush($recorder, $notification);
    }

    private function sendExpoPush(User $user, AppNotification $notification): void
    {
        if (! $user->push_enabled || blank($user->expo_push_token)) {
            return;
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->post('https://exp.host/--/api/v2/push/send', [
                    'to' => $user->expo_push_token,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'sound' => 'default',
                    'data' => array_merge($notification->data ?? [], [
                        'notification_id' => $notification->id,
                        'type' => $notification->type,
                    ]),
                ]);

            if (! $response->successful()) {
                Log::warning('Expo push failed', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push exception', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
