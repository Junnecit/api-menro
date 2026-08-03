<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendRegistrationOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyRegistrationOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\Agency;
use App\Models\Role;
use App\Models\User;
use App\Notifications\RegistrationOtp;
use App\Services\AuthService;
use App\Services\RegistrationOtpService;
use App\Support\PsgcLocation;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private RegistrationOtpService $registrationOtp,
    ) {}

    /**
     * Public list of active admins, for the mobile registration screen's
     * "managing admin" picker. Only id/name are exposed — no auth required,
     * since this is shown before a token exists. Rate-limited to reduce
     * enumeration abuse.
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
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'status' => UserStatus::Pending,
                'email_verified_at' => null,
            ]);

            $code = $this->registrationOtp->issue($user->email);
            $user->notify(new RegistrationOtp($code));

            return response()->json([
                'success' => true,
                'message' => 'Registration started. Please verify your email with the code we sent.',
                'data' => [
                    'user' => new UserResource($user->load(['role', 'admin'])),
                    'token' => null,
                    'pending' => true,
                    'needs_verification' => true,
                    'email' => $user->email,
                ],
            ], 201);
        }

        // Web self-registration creates an Admin + Agency. Account stays Pending
        // until email OTP is verified AND a Super Admin approves it.
        // No Sanctum token is issued at registration.
        $defaultRole = Role::where('slug', 'admin')->first();

        $user = DB::transaction(function () use ($request, $defaultRole) {
            $agencyData = PsgcLocation::applyAddress([
                'initials' => $request->initials,
                'name' => $request->agency_name,
                'type' => $request->type,
                'contact' => $request->contact,
                'email' => $request->agency_email,
                'phone' => $request->phone,
                'region_code' => $request->region_code,
                'province_code' => $request->province_code,
                'municipality_code' => $request->municipality_code,
                'barangay_code' => $request->barangay_code,
                'region_name' => $request->region_name,
                'province_name' => $request->province_name,
                'municipality_name' => $request->municipality_name,
                'barangay_name' => $request->barangay_name,
                'custom_address' => $request->custom_address,
                'status' => 'Active',
            ]);

            $agency = Agency::create($agencyData);

            $personalAddress = implode(', ', array_filter([
                $agencyData['custom_address'] ?? null,
                $agencyData['location'] ?? null,
            ], fn ($value) => filled($value)));

            return User::create([
                'role_id' => $defaultRole?->id,
                'agency_id' => $agency->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address' => $personalAddress !== '' ? $personalAddress : null,
                'status' => UserStatus::Pending,
                'email_verified_at' => null,
            ]);
        });

        $code = $this->registrationOtp->issue($user->email);
        $user->notify(new RegistrationOtp($code));

        return response()->json([
            'success' => true,
            'message' => 'Registration started. Please verify your email with the code we sent.',
            'data' => [
                'user' => new UserResource($user->load(['role', 'agency'])),
                'token' => null,
                'pending' => true,
                'needs_verification' => true,
                'email' => $user->email,
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

        // Self-registered field users and admins must verify email via OTP
        // before login / approval.
        if ($user->email_verified_at === null && in_array($user->role?->slug, ['user', 'admin'], true)) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Please verify your email with the code we sent before signing in.',
                'data' => [
                    'needs_verification' => true,
                    'email' => $user->email,
                ],
            ], 403);
        }

        if ($user->status !== UserStatus::Active) {
            Auth::logout();

            $message = 'Your account is not active.';
            if ($user->status === UserStatus::Pending) {
                $message = $user->isAdmin()
                    ? 'Your account is awaiting approval from a Super Admin.'
                    : 'Your account is awaiting approval from your managing admin.';
            }

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

    public function verifyRegistrationOtp(VerifyRegistrationOtpRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)
            ->where('status', UserStatus::Pending)
            ->whereNull('email_verified_at')
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['user', 'admin']))
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No pending registration found for this email.',
            ], 422);
        }

        if (! $this->registrationOtp->verify($email, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        // Email OTP only confirms ownership of the inbox. Activation is a
        // separate human decision: Super Admin for self-registered admins,
        // managing admin for field users.
        $user->forceFill(['email_verified_at' => now()])->save();

        $message = $user->isAdmin()
            ? 'Email verified. Your account is pending approval from a Super Admin.'
            : 'Email verified. Your account is pending approval from your managing admin.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user' => new UserResource($user->load(['role', 'admin', 'agency'])),
                'token' => null,
                'pending' => true,
                'needs_verification' => false,
            ],
        ]);
    }

    public function resendRegistrationOtp(ResendRegistrationOtpRequest $request): JsonResponse
    {
        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)
            ->where('status', UserStatus::Pending)
            ->whereNull('email_verified_at')
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['user', 'admin']))
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No pending registration found for this email.',
            ], 422);
        }

        $code = $this->registrationOtp->issue($user->email);
        $user->notify(new RegistrationOtp($code));

        return response()->json([
            'success' => true,
            'message' => 'A new verification code has been sent to your email.',
            'data' => [
                'email' => $user->email,
                'needs_verification' => true,
            ],
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
                // Assign the plain password; the User model's 'hashed' cast
                // hashes once. Hash::make here would double-bcrypt and break login.
                $user->forceFill([
                    'password' => $password,
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
