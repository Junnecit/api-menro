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

        $platform = $request->query('platform') ?? $request->header('X-Client-Platform') ?? 'web';
        $redirectUri = $request->query('redirect_uri') ?? $request->query('return_url');
        $statePayload = array_filter([
            'redirect_uri' => $redirectUri,
            'platform' => $platform,
        ]);
        $state = ! empty($statePayload) ? base64_encode(json_encode($statePayload)) : null;

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

        $platform = 'web';
        $targetRedirect = null;
        if ($request->filled('state')) {
            try {
                $decoded = json_decode(base64_decode($request->query('state')), true);
                if (is_array($decoded)) {
                    if (! empty($decoded['redirect_uri'])) {
                        $targetRedirect = $decoded['redirect_uri'];
                    }
                    if (! empty($decoded['platform'])) {
                        $platform = $decoded['platform'];
                    }
                }
            } catch (\Throwable) {}
        }

        if (! $targetRedirect) {
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
            $targetRedirect = "{$frontendUrl}/auth/google/callback";
        }

        $separator = str_contains($targetRedirect, '?') ? '&' : '?';

        try {
            $roleSlug = in_array(strtolower($platform), ['mobile', 'planter', 'user', 'app'], true) ? 'user' : 'admin';
            $user = $this->googleAuthService->findOrCreateUser($roleSlug);

            if ($platform === 'web' && ! $user->isAdminOrAbove()) {
                return redirect("{$targetRedirect}{$separator}error=".urlencode('Access restricted: Mobile/Planter accounts cannot access the Web Administration Portal. Please use the mobile application.'));
            }

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

        $platform = $request->header('X-Client-Platform', 'web');
        if ($platform === 'web' && ! $user->isAdminOrAbove()) {
            return response()->json([
                'success' => false,
                'message' => 'Access restricted: Mobile/Planter accounts cannot access the Web Administration Portal. Please use the mobile application.',
                'is_mobile_account' => true,
            ], 403);
        }

        $token = $this->authService->createToken($user, "google-{$platform}-token");

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
     * Exchange a client-side Google ID Token (from web or mobile app) for a Sanctum token.
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
            'platform' => ['nullable', 'string', 'in:web,mobile,admin,planter,user'],
        ]);

        try {
            $platform = $request->input('platform') ?? $request->header('X-Client-Platform') ?? 'web';
            $roleSlug = in_array(strtolower($platform), ['mobile', 'planter', 'user', 'app'], true) ? 'user' : 'admin';

            $user = $this->googleAuthService->findOrCreateUserFromIdToken(
                idToken: $request->string('id_token')->toString(),
                roleSlug: $roleSlug
            );

            if ($platform === 'web' && ! $user->isAdminOrAbove()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access restricted: Mobile/Planter accounts cannot access the Web Administration Portal. Please use the mobile application.',
                    'is_mobile_account' => true,
                ], 403);
            }

            $token = $this->authService->createToken($user, "google-{$platform}-token");

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
