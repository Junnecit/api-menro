<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function updatePermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        $role->permissions = array_values(array_unique($validated['permissions']));
        $role->save();

        return response()->json([
            'success' => true,
            'message' => "Permissions for role {$role->name} updated successfully.",
            'data' => new RoleResource($role),
        ]);
    }

    public function batchUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*.id' => ['sometimes', 'required_without:roles.*.slug', 'exists:roles,id'],
            'roles.*.slug' => ['sometimes', 'required_without:roles.*.id', 'string'],
            'roles.*.permissions' => ['required', 'array'],
            'roles.*.permissions.*' => ['string'],
        ]);

        $updated = [];
        foreach ($validated['roles'] as $roleData) {
            $role = isset($roleData['id'])
                ? Role::find($roleData['id'])
                : Role::where('slug', $roleData['slug'])->first();

            if ($role) {
                $role->permissions = array_values(array_unique($roleData['permissions']));
                $role->save();
                $updated[] = $role;
            }
        }

        $allRoles = Role::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'Role permissions updated successfully.',
            'data' => RoleResource::collection($allRoles),
        ]);
    }

    public function resetPermissions(Role $role): JsonResponse
    {
        $role->permissions = null;
        $role->save();

        return response()->json([
            'success' => true,
            'message' => "Permissions for role {$role->name} reset to defaults.",
            'data' => new RoleResource($role),
        ]);
    }

    public function resetAll(): JsonResponse
    {
        Role::query()->update(['permissions' => null]);
        $roles = Role::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'All role permissions reset to system defaults.',
            'data' => RoleResource::collection($roles),
        ]);
    }
}

