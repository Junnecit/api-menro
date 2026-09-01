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

    public function redirect(Request $request): JsonResponse|RedirectResponse
    {
        if (! $this->googleAuthService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI in your .env file.',
            ], 503);
        }

        $redirectUri = $request->query('redirect_uri') ?? $request->query('return_url');
        $state = $redirectUri ? base64_encode(json_encode(['redirect_uri' => $redirectUri])) : null;

        return redirect($this->googleAuthService->getRedirectUrl($state));
    }

    public function callback(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->googleAuthService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Google OAuth is not configured.',
            ], 503);
        }

        $targetRedirect = null;
        if ($request->filled('state')) {
            try {
                $decoded = json_decode(base64_decode($request->query('state')), true);
                if (is_array($decoded) && ! empty($decoded['redirect_uri'])) {
                    $targetRedirect = $decoded['redirect_uri'];
                }
            } catch (\Throwable) {}
        }

        if (! $targetRedirect) {
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
            $targetRedirect = "{$frontendUrl}/auth/google/callback";
        }

        $separator = str_contains($targetRedirect, '?') ? '&' : '?';

        try {
            $user = $this->googleAuthService->findOrCreateUser();
            // Never put the Sanctum token in the redirect URL — use a short-lived
            // one-time code the SPA exchanges via POST.
            $code = $this->oauthCodes->issue($user);

            return redirect("{$targetRedirect}{$separator}code={$code}");
        } catch (UnauthorizedGoogleEmailException|InactiveGoogleAccountException $e) {
            Log::warning('Google auth rejected: '.$e->getMessage());

            return redirect("{$targetRedirect}{$separator}error=".urlencode($e->getMessage()));
        } catch (\Throwable $e) {
            Log::error('Google auth callback failed: '.$e->getMessage(), ['exception' => $e]);

            return redirect("{$targetRedirect}{$separator}error=".urlencode('Google authentication failed. Please try again.'));
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

    /**
     * Exchange a client-side Google ID Token (from mobile app) for a Sanctum token.
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $user = $this->googleAuthService->findOrCreateUserFromIdToken(
                $request->string('id_token')->toString()
            );

            $token = $this->authService->createToken($user, 'google-auth-token');

            return response()->json([
                'success' => true,
                'message' => 'Google authentication successful.',
                'data' => [
                    'user' => new UserResource($user->load('role')),
                    'token' => $token,
                ],
            ]);
        } catch (UnauthorizedGoogleEmailException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (InactiveGoogleAccountException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Google ID token login failed: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed. Please try again.',
            ], 500);
        }
    }
}
