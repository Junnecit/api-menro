<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class GoogleOAuthCodeService
{
    private const TTL_SECONDS = 120;

    private function store(): Repository
    {
        $default = Config::get('cache.default', 'file');
        // 'array' is in-memory per-request and loses codes across redirects.
        // Always fall back to 'file' if 'array' is configured.
        if ($default === 'array') {
            return Cache::store('file');
        }

        return Cache::store($default);
    }

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
        $this->store()->put($this->cacheKey($code), $user->id, self::TTL_SECONDS);

        return $code;
    }

    /**
     * Consume a one-time code and return the associated user, or null.
     */
    public function consume(string $code): ?User
    {
        $key = $this->cacheKey($code);
        $userId = $this->store()->pull($key);

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}
