<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ReportCenterPolicy
{
    // Shared workspace for Super Admins and field users; plain admins are excluded.
    public function viewAny(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function view(User $user, Model $item): bool
    {
        return ! $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function update(User $user, Model $item): bool
    {
        return ! $user->isAdmin();
    }

    public function delete(User $user, Model $item): bool
    {
        return ! $user->isAdmin();
    }

    public function restore(User $user, Model $item): bool
    {
        return ! $user->isAdmin();
    }

    public function forceDelete(User $user, Model $item): bool
    {
        return ! $user->isAdmin();
    }
}
