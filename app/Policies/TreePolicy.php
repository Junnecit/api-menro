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
        // Monitors sync/edit only; planters and admins plant/add data.
        return ! $user->isMonitor();
    }

    public function update(User $user, Tree $tree): bool
    {
        // Planters can only add data; they cannot edit existing trees.
        if ($user->isPlanter()) {
            return false;
        }

        return $this->owns($user, $tree);
    }

    public function delete(User $user, Tree $tree): bool
    {
        // Planters cannot delete trees.
        if ($user->isPlanter()) {
            return false;
        }

        return $this->owns($user, $tree);
    }

    /**
     * A user may act on a tree only if they own it or are authorized.
     * Super Admins bypass the ownership check entirely.
     * Planters own/view only the trees they recorded themselves.
     * An admin owns every tree recorded in their pool or tagged with their agency.
     * A monitor can monitor and edit all trees planted under their assigned admin.
     * Must stay in sync with Tree::scopeOwnedBy.
     */
    private function owns(User $user, Tree $tree): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isPlanter()) {
            return (int) $tree->recorded_by_id === (int) $user->id;
        }

        if (in_array($tree->recorded_by_id, $user->agencyPoolUserIds(), true)) {
            return true;
        }

        // Monitors with assigned admin can access trees recorded by that admin or their planters
        if ($user->isMonitor() && $user->admin_id) {
            if ((int) $tree->recorded_by_id === (int) $user->admin_id) {
                return true;
            }
            $recorder = $tree->relationLoaded('recordedBy')
                ? $tree->recordedBy
                : User::find($tree->recorded_by_id);

            if ($recorder && (int) $recorder->admin_id === (int) $user->admin_id) {
                return true;
            }
        }

        $agencyId = $user->effectiveAgencyId();

        return $agencyId !== null && (int) $tree->agency_id === (int) $agencyId;
    }
}
