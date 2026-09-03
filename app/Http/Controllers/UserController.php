<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Notifications\UserApproved;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function options(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = User::query()->orderBy('name');

        // Inspector picker must stay inside the caller's agency pool so field
        // users never see accounts from other agencies.
        if (! $user->isSuperAdmin()) {
            $query->whereIn('id', $user->agencyPoolUserIds());
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(['id', 'name']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, User::query(), trashed: false)
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, User::onlyTrashed(), trashed: true)
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $role = Role::findOrFail($request->role_id);

        if ($role->isSuperAdmin() && ! $request->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot assign the Super Admin role.',
            ], 403);
        }

        // Only a Super Admin can assign or promote to the Admin role.
        if ($role->slug === 'admin' && ! $request->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts must be a Super Admin promotion only.',
            ], 403);
        }

        $data = $request->validated();

        if ($role->needsManagingAdmin()) {
            // A plain admin manages only their own pool, so every field user
            // they create is assigned to them; a Super Admin may pick any admin.
            if ($request->user()->isAdmin()) {
                $data['admin_id'] = $request->user()->id;
            }
        } else {
            // Admins and Super Admins are not managed by anyone.
            $data['admin_id'] = null;
        }

        // An agency link only makes sense for admin accounts.
        if ($role->slug !== 'admin') {
            $data['agency_id'] = null;
        }

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource($user->load(['role', 'admin', 'agency'])),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load(['role', 'admin', 'agency'])),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        if ($request->filled('role_id')) {
            $role = Role::findOrFail($request->role_id);

            if ($role->isSuperAdmin() && ! $request->user()->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot assign the Super Admin role.',
                ], 403);
            }

            // Admin accounts must be a Super Admin promotion only.
            if ($role->slug === 'admin' && ! $request->user()->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin accounts must be a Super Admin promotion only.',
                ], 403);
            }
        }

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // A managing admin only makes sense for field roles (planter/monitor).
        // Admins and Super Admins are not managed by anyone.
        $targetRole = $request->filled('role_id')
            ? Role::find($request->role_id)
            : $user->role;
        $targetRoleSlug = $targetRole?->slug;
        if ($targetRole && ! $targetRole->needsManagingAdmin() && array_key_exists('admin_id', $data)) {
            $data['admin_id'] = null;
        } elseif ($targetRole && $targetRole->needsManagingAdmin() && $request->user()->isAdmin()) {
            $data['admin_id'] = $request->user()->id;
        }

        // An agency link only makes sense for admin accounts.
        if ($targetRoleSlug !== 'admin' && array_key_exists('agency_id', $data)) {
            $data['agency_id'] = null;
        }

        $wasPending = $user->status === UserStatus::Pending;
        $roleChanged = $request->filled('role_id') && (int) $request->role_id !== (int) $user->role_id;
        $oldRole = $user->role;
        $oldRoleName = $oldRole?->name ?? 'User';
        $passwordChanged = ! empty($data['password']);
        $currentStatus = $user->status instanceof UserStatus ? $user->status->value : (string) $user->status;
        $newStatus = array_key_exists('status', $data)
            ? ($data['status'] instanceof UserStatus ? $data['status']->value : (string) $data['status'])
            : $currentStatus;
        $statusChanged = $newStatus !== $currentStatus;
        $isDeactivating = $statusChanged && in_array($newStatus, ['inactive', 'suspended'], true);

        if ($roleChanged) {
            $data['relogin_required'] = true;
            $data['relogin_reason'] = 'role_updated';
        } elseif ($isDeactivating) {
            $data['relogin_required'] = true;
            $data['relogin_reason'] = 'account_disabled';
        } elseif ($passwordChanged) {
            $data['relogin_required'] = true;
            $data['relogin_reason'] = 'password_changed';
        }

        $user->update($data);

        if ($roleChanged && $targetRole) {
            app(\App\Services\UserRoleNotifier::class)->notifyRoleChanged(
                $user,
                $targetRole,
                $oldRoleName,
                $request->user()
            );
        }

        if ($isDeactivating) {
            app(\App\Services\UserRoleNotifier::class)->notifyAccountDisabled(
                $user,
                $request->user()
            );
        }

        if ($wasPending && $user->status === UserStatus::Active) {
            // Approval implies the account may sign in; backfill verification so
            // login is not blocked by a missing OTP step after activation.
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            dispatch(function () use ($user) {
                try {
                    $user->notify(new UserApproved);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('UserApproved email dispatch failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => new UserResource($user->fresh()->load(['role', 'admin', 'agency'])),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User moved to trash.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $user);

        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully.',
            'data' => new UserResource($user->fresh()->load(['role', 'admin', 'agency'])),
        ]);
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $user);


        $user->tokens()->delete();
        $user->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'User permanently deleted.',
        ]);
    }

    private function filteredQuery(Request $request, \Illuminate\Database\Eloquent\Builder $query, bool $trashed = false)
    {
        // Directory lists show admin/super-admin roots with nested planters
        // (managed_users), matching the planting-request parent/child UX.
        // Nested payloads are for the unfiltered/paginated directory; role=
        // filters (e.g. admins picker) stay flat.
        $nest = ! $request->filled('role') && ! $request->boolean('flat');

        if ($nest) {
            $query->where(function ($q) {
                $q->whereHas('role', fn ($r) => $r->whereIn('slug', ['super-admin', 'admin']))
                    ->orWhere(function ($orphan) {
                        // Unassigned field users still appear as standalone roots.
                        $orphan->whereNull('admin_id')
                            ->whereHas('role', fn ($r) => $r->whereIn('slug', ['user', 'monitor', 'other']));
                    });
            });

            $query->with([
                'role',
                'admin:id,name',
                'agency:id,name',
                'managedUsers' => function ($managed) use ($trashed) {
                    if ($trashed) {
                        $managed->onlyTrashed();
                    }
                    $managed->with(['role', 'admin:id,name', 'agency:id,name'])
                        ->orderBy('name');
                },
            ]);
        } else {
            $query->with(['role', 'admin:id,name', 'agency:id,name']);
        }

        $query->visibleTo($request->user());

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($nest) {
                $query->where(function ($q) use ($status, $trashed) {
                    $q->where('status', $status)
                        ->orWhereHas('managedUsers', function ($child) use ($status, $trashed) {
                            if ($trashed) {
                                $child->onlyTrashed();
                            }
                            $child->where('status', $status);
                        });
                });
            } else {
                $query->status($status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search, $nest, $trashed) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

                if ($nest) {
                    $q->orWhereHas('managedUsers', function ($child) use ($search, $trashed) {
                        if ($trashed) {
                            $child->onlyTrashed();
                        }
                        $child->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($request->filled('role')) {
            $query->roleSlug($request->query('role'));
        }

        // Pin Super Admin first, then newest accounts.
        return $query
            ->orderByRaw("CASE WHEN EXISTS (
                SELECT 1 FROM roles WHERE roles.id = users.role_id AND roles.slug = 'super-admin'
            ) THEN 0 ELSE 1 END")
            ->latest();
    }

    private function paginatedResponse($query): JsonResponse
    {
        $users = $query->paginate(request()->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }
}
