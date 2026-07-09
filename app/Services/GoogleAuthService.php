<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Exceptions\InactiveGoogleAccountException;
use App\Exceptions\UnauthorizedGoogleEmailException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleAuthService
{
    public function isConfigured(): bool
    {
        return ! empty(Config::get('services.google.client_id'))
            && ! empty(Config::get('services.google.client_secret'));
    }

    private function provider(): GoogleProvider
    {
        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');

        return $driver;
    }

    public function getRedirectUrl(): string
    {
        return $this->provider()->stateless()->redirect()->getTargetUrl();
    }

    public function findOrCreateUser(): User
    {
        $googleUser = $this->provider()->stateless()->user();

        if (! $this->isEmailAuthorized($googleUser->getEmail())) {
            throw new UnauthorizedGoogleEmailException($googleUser->getEmail());
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            if ($user->status !== UserStatus::Active) {
                throw new InactiveGoogleAccountException($user->status);
            }

            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            return $user;
        }

        $defaultRole = Role::where('slug', 'user')->first();

        return User::create([
            'role_id' => $defaultRole?->id,
            'name' => $googleUser->getName() ?? $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);
    }

    private function isEmailAuthorized(string $email): bool
    {
        $allowedEmails = Config::get('services.google.allowed_emails', []);
        $allowedDomains = Config::get('services.google.allowed_domains', []);

        if (empty($allowedEmails) && empty($allowedDomains)) {
            return true;
        }

        $email = strtolower($email);

        if (in_array($email, $allowedEmails, true)) {
            return true;
        }

        $domain = substr(strrchr($email, '@'), 1) ?: '';

        return in_array($domain, $allowedDomains, true);
    }
}
