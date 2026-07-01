<?php

namespace App\Policies;

use App\Models\Tree;
use App\Models\User;

class TreePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tree $tree): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tree $tree): bool
    {
        return $user->isAdminOrAbove() || $tree->recorded_by_id === $user->id;
    }

    public function delete(User $user, Tree $tree): bool
    {
        return $user->isAdminOrAbove() || $tree->recorded_by_id === $user->id;
    }
}
