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
     * ownership check entirely. An admin owns every tree recorded by a managed
     * user, plus — because an admin account represents a stakeholder agency —
     * every tree tagged with their agency. A regular user owns only the trees
     * they recorded. Must stay in sync with Tree::scopeOwnedBy.
     */
    private function owns(User $user, Tree $tree): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (in_array($tree->recorded_by_id, $user->visibleUserIds(), true)) {
            return true;
        }

        return $user->isAdmin()
            && $user->agency_id !== null
            && $tree->agency_id === $user->agency_id;
    }
}
