<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Role;
use App\Models\User;
use App\Notifications\UserRoleUpdated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserRoleNotifier
{
    /**
     * Dispatch in-app notification, Expo push notification, and email when a user's role is updated.
     */
    public function notifyRoleChanged(User $user, Role $newRole, string $oldRoleName, User $actor): void
    {
        $roleName = $newRole->name;
        $title = "Account Role Updated";
        $body = sprintf(
            'Your account role was updated from %s to %s by %s.',
            $oldRoleName,
            $roleName,
            $actor->name
        );

        if ($newRole->slug === 'monitor') {
            $body .= ' You now have monitoring and tree inspection access.';
        } elseif ($newRole->slug === 'user') {
            $body .= ' You now have planter permissions for recording trees.';
        }

        // 1. Create in-app notification (instant database write)
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => 'role_updated',
            'title' => $title,
            'body' => $body,
            'data' => [
                'old_role' => $oldRoleName,
                'new_role' => $roleName,
                'new_role_slug' => $newRole->slug,
                'updated_by_id' => $actor->id,
                'updated_by_name' => $actor->name,
            ],
        ]);

        // 2 & 3. Defer mobile Expo push and SMTP email after the HTTP response is sent
        // to ensure immediate API response for the administrator.
        dispatch(function () use ($user, $notification, $roleName, $newRole, $oldRoleName, $actor) {
            $this->sendExpoPush($user, $notification, $roleName);

            try {
                $user->notify(new UserRoleUpdated($newRole, $oldRoleName, $actor));
            } catch (\Throwable $e) {
                Log::warning('UserRoleUpdated email dispatch failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }

    /**
     * Dispatch in-app notification and Expo push notification when a user account is disabled.
     */
    public function notifyAccountDisabled(User $user, User $actor): void
    {
        $title = "Account Deactivated";
        $body = sprintf('Your account has been deactivated by %s. Active sessions have been logged out.', $actor->name);

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => 'account_disabled',
            'title' => $title,
            'body' => $body,
            'data' => [
                'updated_by_id' => $actor->id,
                'updated_by_name' => $actor->name,
                'status' => 'inactive',
            ],
        ]);

        dispatch(function () use ($user, $notification) {
            $this->sendExpoPush($user, $notification, 'Deactivated');
        })->afterResponse();
    }

    private function sendExpoPush(User $user, AppNotification $notification, string $roleName): void
    {
        if (! $user->push_enabled || blank($user->expo_push_token)) {
            return;
        }

        try {
            $response = Http::timeout(4)
                ->connectTimeout(2)
                ->acceptJson()
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
                Log::warning('Expo push failed for role update', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push exception for role update', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
