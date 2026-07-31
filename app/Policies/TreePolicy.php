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
     * user, plus every tree tagged with their agency. A managed field user may
     * view/edit trees in their admin's agency pool. Unassigned users own only
     * trees they recorded. Must stay in sync with Tree::scopeOwnedBy.
     */
    private function owns(User $user, Tree $tree): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (in_array($tree->recorded_by_id, $user->agencyPoolUserIds(), true)) {
            return true;
        }

        $agencyId = $user->effectiveAgencyId();

        return $agencyId !== null && $tree->agency_id === $agencyId;
    }
}
