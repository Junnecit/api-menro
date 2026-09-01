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
        // Monitors sync/edit only; planters and admins plant.
        return ! $user->isMonitor();
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
     * ownership check entirely. Planters own only the trees they recorded themselves.
     * An admin owns every tree recorded by a managed user, plus every tree
     * tagged with their agency. A monitor may view trees in their admin's
     * agency pool. Must stay in sync with Tree::scopeOwnedBy.
     */
    private function owns(User $user, Tree $tree): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isPlanter()) {
            return $tree->recorded_by_id === $user->id;
        }

        if (in_array($tree->recorded_by_id, $user->agencyPoolUserIds(), true)) {
            return true;
        }

        $agencyId = $user->effectiveAgencyId();

        return $agencyId !== null && $tree->agency_id === $agencyId;
    }
}
