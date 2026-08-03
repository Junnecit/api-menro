<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GoogleOAuthCodeService
{
    private const TTL_SECONDS = 120;

    private function cacheKey(string $code): string
    {
        return 'google_oauth_exchange:'.$code;
    }

    /**
     * Store a one-time exchange code that maps to a user id.
     */
    public function issue(User $user): string
    {
        $code = Str::random(64);
        Cache::put($this->cacheKey($code), $user->id, self::TTL_SECONDS);

        return $code;
    }

    /**
     * Consume a one-time code and return the associated user, or null.
     */
    public function consume(string $code): ?User
    {
        $key = $this->cacheKey($code);
        $userId = Cache::pull($key);

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}
