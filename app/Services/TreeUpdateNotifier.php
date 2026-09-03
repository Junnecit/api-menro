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
     * Notify the planter who recorded the tree, plus assigned admin/inspector when monitoring updates occur.
     */
    public function notifyRecorder(Tree $tree, User $actor): void
    {
        $species = $tree->species ?: $tree->common_name ?: 'Tree';
        $code = $tree->tree_code ?: ('#' . $tree->id);
        $statusValue = $tree->status instanceof \BackedEnum
            ? $tree->status->value
            : (string) ($tree->status ?: 'updated');
        $location = $tree->barangay ? " at {$tree->barangay}" : '';

        // Status-specific title & body
        [$title, $body] = match ($statusValue) {
            'alive' => [
                "Tree {$code} Monitored: Alive",
                sprintf('%s inspected tree %s (%s)%s. Status confirmed: Alive and healthy.', $actor->name, $species, $code, $location),
            ],
            'dead' => [
                "Tree {$code} Alert: Dead",
                sprintf('%s inspected tree %s (%s)%s. Tree has been marked as Dead.', $actor->name, $species, $code, $location),
            ],
            'need_replacement' => [
                "Tree {$code} Alert: Needs Replacement",
                sprintf('%s inspected tree %s (%s)%s. Tree status: Needs Replacement. Action required.', $actor->name, $species, $code, $location),
            ],
            default => [
                "Tree {$code} Updated",
                sprintf('%s updated tree %s (%s)%s to "%s".', $actor->name, $species, $code, $location, ucwords(str_replace('_', ' ', $statusValue))),
            ],
        };

        // Determine notification recipients:
        // 1. The planter who recorded it
        // 2. The managing admin (if any)
        $recipientIds = collect([
            $tree->recorded_by_id,
            $tree->inspector_id,
            $tree->recordedBy?->admin_id,
        ])->filter()->unique();

        // If no external recipients found, send to the recorder or actor so testing never silently fails
        if ($recipientIds->isEmpty()) {
            $recipientIds = collect([$actor->id]);
        }

        foreach ($recipientIds as $userId) {
            $user = User::query()->find($userId);
            if (! $user) {
                continue;
            }

            $notification = AppNotification::create([
                'user_id' => $user->id,
                'type' => 'tree_updated',
                'title' => $title,
                'body' => $body,
                'data' => [
                    'tree_id' => $tree->id,
                    'tree_code' => $tree->tree_code,
                    'species' => $tree->species ?: $tree->common_name,
                    'status' => $statusValue,
                    'status_label' => ucwords(str_replace('_', ' ', $statusValue)),
                    'updated_by_id' => $actor->id,
                    'updated_by_name' => $actor->name,
                ],
            ]);

            $this->sendExpoPush($user, $notification);
        }
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
                    'priority' => 'high',
                    'data' => array_merge($notification->data ?? [], [
                        'notification_id' => $notification->id,
                        'type' => $notification->type,
                    ]),
                ]);

            if (! $response->successful()) {
                Log::warning('Expo push failed for tree update', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push exception for tree update', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
