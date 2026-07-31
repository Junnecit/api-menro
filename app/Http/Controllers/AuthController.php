<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * Public list of active admins, for the mobile registration screen's
     * "managing admin" picker. Only id/name are exposed — no auth required,
     * since this is shown before a token exists.
     */
    public function admins(): JsonResponse
    {
        $admins = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))
            ->where('status', UserStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $admins,
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        // Mobile (field-user) registration includes admin_id: the registrant
        // picked a managing admin, so the account is created as a regular
        // User and held as Pending until that admin approves it — no token
        // is issued, so it cannot be used to log in until then.
        if ($request->filled('admin_id')) {
            $userRole = Role::where('slug', 'user')->first();

            $user = User::create([
                'role_id' => $userRole?->id,
                'admin_id' => $request->admin_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'status' => UserStatus::Pending,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Your account is pending approval from your managing admin.',
                'data' => [
                    'user' => new UserResource($user->load(['role', 'admin'])),
                    'token' => null,
                    'pending' => true,
                ],
            ], 201);
        }

        // Every self-registered web account becomes a Stakeholder — the 'admin'
        // slug, so it lands on the admin dashboard with full admin powers
        // immediately, but presented as "Stakeholder". The role is fixed
        // server-side (not chosen by the registrant) to avoid privilege
        // escalation; elevated Super Admin access is still granted only through
        // user management.
        $defaultRole = Role::where('slug', 'admin')->first();

        $user = User::create([
            'role_id' => $defaultRole?->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'status' => UserStatus::Active,
        ]);

        $token = $this->authService->createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => new UserResource($user->load('role')),
                'token' => $token,
                'pending' => false,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== UserStatus::Active) {
            Auth::logout();

            $message = $user->status === UserStatus::Pending
                ? 'Your account is awaiting approval from your managing admin.'
                : 'Your account is not active.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        $token = $this->authService->createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user->load('role')),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeCurrentToken($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource(
                $request->user()->load(['role', 'admin.agency', 'agency'])
            ),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __($status),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __($status),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update(['password' => $request->password]);
        $user->tokens()->delete();
        $token = $this->authService->createToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => ['token' => $token],
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->authService->deleteAccount($user, $request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }
}
