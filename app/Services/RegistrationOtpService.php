<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class RegistrationOtpService
{
    private const TTL_SECONDS = 600;

    public function cacheKey(string $email): string
    {
        return 'registration_otp:'.strtolower(trim($email));
    }

    /**
     * Generate a 6-digit OTP, store its hash in cache, and return the plain code.
     */
    public function issue(string $email): string
    {
        $code = (string) random_int(100000, 999999);

        Cache::put(
            $this->cacheKey($email),
            Hash::make($code),
            self::TTL_SECONDS
        );

        return $code;
    }

    public function verify(string $email, string $code): bool
    {
        $hashed = Cache::get($this->cacheKey($email));

        if (! is_string($hashed) || $hashed === '') {
            return false;
        }

        if (! Hash::check($code, $hashed)) {
            return false;
        }

        Cache::forget($this->cacheKey($email));

        return true;
    }

    public function forget(string $email): void
    {
        Cache::forget($this->cacheKey($email));
    }
}
