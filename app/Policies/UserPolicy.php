<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminOrAbove();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdminOrAbove() && $user->canManageUser($model);
    }

    public function create(User $user): bool
    {
        return $user->isAdminOrAbove();
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->isAdminOrAbove()) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->canManageUser($model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if (! $user->isAdminOrAbove()) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->canManageUser($model);
    }

    public function restore(User $user, User $model): bool
    {
        if (! $user->isAdminOrAbove()) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->canManageUser($model);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->restore($user, $model);
    }
}
