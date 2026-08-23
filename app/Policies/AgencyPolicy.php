<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;

class AgencyPolicy
{
    public function viewAny(User $user): bool
    {
        // All authenticated roles may list agencies (needed for forms/options).
        return true;
    }

    public function view(User $user, Agency $agency): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Registry create stays with Super Admin (admins get an agency at signup).
        return $user->isSuperAdmin();
    }

    public function update(User $user, Agency $agency): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admins may edit their own linked agency details.
        return $user->isAdmin()
            && $user->agency_id !== null
            && (int) $user->agency_id === (int) $agency->id;
    }

    public function delete(User $user, Agency $agency): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Agency $agency): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Agency $agency): bool
    {
        return $user->isSuperAdmin();
    }
}
