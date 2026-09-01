<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Exceptions\InactiveGoogleAccountException;
use App\Exceptions\UnauthorizedGoogleEmailException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    public function getRedirectUrl(?string $state = null): string
    {
        $provider = $this->provider()->stateless();
        if ($state) {
            $provider->with(['state' => $state]);
        }

        return $provider->redirect()->getTargetUrl();
    }

    public function findOrCreateUser(): User
    {
        $googleUser = $this->provider()->stateless()->user();

        return $this->findOrCreateUserFromData(
            googleId: $googleUser->getId(),
            email: $googleUser->getEmail(),
            name: $googleUser->getName()
        );
    }

    /**
     * Verify Google ID token against Google's tokeninfo API and find/create user.
     */
    public function findOrCreateUserFromIdToken(string $idToken): User
    {
        $http = Http::timeout(10);
        if (app()->environment('local', 'testing')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->successful()) {
            Log::warning('Google ID token verification failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \InvalidArgumentException('Invalid or expired Google token.');
        }

        $payload = $response->json();

        $email = $payload['email'] ?? null;
        $googleId = $payload['sub'] ?? null;
        $name = $payload['name'] ?? null;
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $googleId || ! $email) {
            throw new \InvalidArgumentException('Google token payload is missing email or user ID.');
        }

        if (! $emailVerified) {
            throw new \InvalidArgumentException('Google account email is not verified.');
        }

        return $this->findOrCreateUserFromData(
            googleId: $googleId,
            email: $email,
            name: $name
        );
    }

    /**
     * Common user lookup, status validation, and account creation logic.
     */
    public function findOrCreateUserFromData(string $googleId, string $email, ?string $name = null): User
    {
        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if ($user->status !== UserStatus::Active) {
                throw new InactiveGoogleAccountException($user->status);
            }

            if (! $user->google_id) {
                $user->update(['google_id' => $googleId]);
            }

            return $user;
        }

        // New Google accounts only — fail closed outside local when allowlists are empty.
        if (! $this->isEmailAuthorized($email)) {
            throw new UnauthorizedGoogleEmailException($email);
        }

        $defaultRole = Role::where('slug', 'user')->first() ?? Role::first();

        $baseName = trim($name ?? '') ?: explode('@', $email)[0];
        $finalName = $baseName;
        $counter = 1;
        while (User::where('name', $finalName)->exists()) {
            $finalName = "{$baseName} ({$counter})";
            $counter++;
        }

        return User::create([
            'role_id' => $defaultRole?->id,
            'name' => $finalName,
            'email' => $email,
            'google_id' => $googleId,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);
    }

    private function isEmailAuthorized(string $email): bool
    {
        $allowedEmails = Config::get('services.google.allowed_emails', []);
        $allowedDomains = Config::get('services.google.allowed_domains', []);

        // Fail closed outside local: empty allowlists mean no new Google sign-ins.
        // Existing users are still matched earlier in findOrCreateUser by google_id/email.
        if (empty($allowedEmails) && empty($allowedDomains)) {
            return app()->environment('local', 'testing');
        }

        $email = strtolower($email);

        if (in_array($email, $allowedEmails, true)) {
            return true;
        }

        $domain = substr(strrchr($email, '@'), 1) ?: '';

        return in_array($domain, $allowedDomains, true);
    }
}
