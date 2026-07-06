<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function options(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, User::query())
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, User::onlyTrashed())
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

        $data = $request->validated();

        if ($role->slug === 'user') {
            // A plain admin manages only their own pool, so every user they
            // create is assigned to them; a Super Admin may pick any admin.
            if ($request->user()->isAdmin()) {
                $data['admin_id'] = $request->user()->id;
            }
        } else {
            // Admins and Super Admins are not managed by anyone.
            $data['admin_id'] = null;
        }

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource($user->load(['role', 'admin'])),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load(['role', 'admin'])),
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
        }

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // A managing admin only makes sense for regular users. If this account
        // is (or is becoming) an admin/super-admin, it cannot have a manager.
        $targetRoleSlug = $request->filled('role_id')
            ? Role::find($request->role_id)?->slug
            : $user->role?->slug;
        if ($targetRoleSlug !== 'user' && array_key_exists('admin_id', $data)) {
            $data['admin_id'] = null;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => new UserResource($user->fresh()->load(['role', 'admin'])),
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
            'data' => new UserResource($user->fresh()->load(['role', 'admin'])),
        ]);
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $user);

        if ($user->testItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot permanently delete a user with linked test items.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'User permanently deleted.',
        ]);
    }

    private function filteredQuery(Request $request, $query)
    {
        return $query->with('role')
            ->visibleTo($request->user())
            ->search($request->query('search'))
            ->roleSlug($request->query('role'))
            ->status($request->query('status'))
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
