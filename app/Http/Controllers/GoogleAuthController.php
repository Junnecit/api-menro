<?php

namespace App\Http\Controllers;

use App\Exceptions\InactiveGoogleAccountException;
use App\Exceptions\UnauthorizedGoogleEmailException;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\GoogleAuthService;
use App\Services\GoogleOAuthCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function __construct(
        private GoogleAuthService $googleAuthService,
        private AuthService $authService,
        private GoogleOAuthCodeService $oauthCodes,
    ) {}

    public function redirect(): JsonResponse|RedirectResponse
    {
        if (! $this->googleAuthService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI in your .env file.',
            ], 503);
        }

        return redirect($this->googleAuthService->getRedirectUrl());
    }

    public function callback(): RedirectResponse|JsonResponse
    {
        if (! $this->googleAuthService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Google OAuth is not configured.',
            ], 503);
        }

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        try {
            $user = $this->googleAuthService->findOrCreateUser();
            // Never put the Sanctum token in the redirect URL — use a short-lived
            // one-time code the SPA exchanges via POST.
            $code = $this->oauthCodes->issue($user);

            return redirect("{$frontendUrl}/auth/google/callback?code={$code}");
        } catch (UnauthorizedGoogleEmailException|InactiveGoogleAccountException $e) {
            Log::warning('Google auth rejected: '.$e->getMessage());

            return redirect("{$frontendUrl}/auth/google/callback?error=".urlencode($e->getMessage()));
        } catch (\Throwable $e) {
            Log::error('Google auth callback failed: '.$e->getMessage(), ['exception' => $e]);

            return redirect("{$frontendUrl}/auth/google/callback?error=".urlencode('Google authentication failed. Please try again.'));
        }
    }

    public function exchange(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'min:32', 'max:128'],
        ]);

        $user = $this->oauthCodes->consume($request->string('code')->toString());

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired Google sign-in code.',
            ], 422);
        }

        $token = $this->authService->createToken($user, 'google-auth-token');

        return response()->json([
            'success' => true,
            'message' => 'Google authentication successful.',
            'data' => [
                'user' => new UserResource($user->load('role')),
                'token' => $token,
            ],
        ]);
    }
}
