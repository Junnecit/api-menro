<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;

class AgencyPolicy
{
    public function viewAny(User $user): bool
    {
        // Admins may still read agencies (needed for planting request forms),
        // but cannot manage the stakeholder registry.
        return true;
    }

    public function view(User $user, Agency $agency): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function update(User $user, Agency $agency): bool
    {
        return ! $user->isAdmin();
    }

    public function delete(User $user, Agency $agency): bool
    {
        return ! $user->isAdmin();
    }

    public function restore(User $user, Agency $agency): bool
    {
        return ! $user->isAdmin();
    }

    public function forceDelete(User $user, Agency $agency): bool
    {
        return ! $user->isAdmin();
    }
}
