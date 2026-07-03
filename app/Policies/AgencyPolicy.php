<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;

class AgencyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Agency $agency): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Agency $agency): bool
    {
        return true;
    }

    public function delete(User $user, Agency $agency): bool
    {
        return true;
    }

    public function restore(User $user, Agency $agency): bool
    {
        return true;
    }

    public function forceDelete(User $user, Agency $agency): bool
    {
        return true;
    }
}
