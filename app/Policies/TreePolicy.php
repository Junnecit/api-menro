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
        return $this->owns($user, $tree);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tree $tree): bool
    {
        return $this->owns($user, $tree);
    }

    public function delete(User $user, Tree $tree): bool
    {
        return $this->owns($user, $tree);
    }

    /**
     * A user may act on a tree only if they own it. Super Admins bypass the
     * ownership check entirely; an admin also owns every tree recorded by a
     * managed user, while a regular user owns only the trees they recorded.
     */
    private function owns(User $user, Tree $tree): bool
    {
        return $user->isSuperAdmin()
            || in_array($tree->recorded_by_id, $user->visibleUserIds(), true);
    }
}
