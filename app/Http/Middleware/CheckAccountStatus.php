<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     * Ensure the authenticated user's account is active and does not have a pending re-login requirement.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // 1. Check if the account has been disabled / inactivated / suspended
        if ($user->status !== UserStatus::Active) {
            // Revoke all tokens so session cannot be reused
            $user->tokens()->delete();

            $statusName = $user->status instanceof UserStatus ? $user->status->value : (string) $user->status;
            $message = $statusName === 'suspended'
                ? 'Your account has been suspended. You have been logged out. Please contact your administrator.'
                : 'Your account has been disabled. You have been logged out. Please contact your administrator.';

            return response()->json([
                'success' => false,
                'code' => 'ACCOUNT_DISABLED',
                'message' => $message,
            ], 403);
        }

        // 2. Check if a re-login is required (e.g. role updated to inherit new permissions)
        if ($user->relogin_required) {
            // Revoke all tokens so the user re-authenticates with fresh permissions
            $user->tokens()->delete();

            // Clear re-login flags
            $user->forceFill([
                'relogin_required' => false,
                'relogin_reason' => null,
            ])->saveQuietly();

            return response()->json([
                'success' => false,
                'code' => 'ACCOUNT_UPDATED',
                'message' => 'Your account is updated and you are advised to re-login.',
            ], 401);
        }

        return $next($request);
    }
}
