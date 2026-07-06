<?php

namespace App\Policies;

use App\Models\Request as PlantingRequest;
use App\Models\User;

class RequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlantingRequest $plantingRequest): bool
    {
        return $this->owns($user, $plantingRequest);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlantingRequest $plantingRequest): bool
    {
        return $this->owns($user, $plantingRequest);
    }

    public function delete(User $user, PlantingRequest $plantingRequest): bool
    {
        return $this->owns($user, $plantingRequest);
    }

    public function restore(User $user, PlantingRequest $plantingRequest): bool
    {
        return $this->owns($user, $plantingRequest);
    }

    public function forceDelete(User $user, PlantingRequest $plantingRequest): bool
    {
        return $this->owns($user, $plantingRequest);
    }

    /**
     * A user may act on a request only if they own it. Super Admins bypass the
     * ownership check entirely; an admin also owns every managed user's request.
     */
    private function owns(User $user, PlantingRequest $plantingRequest): bool
    {
        return $user->isSuperAdmin()
            || in_array($plantingRequest->user_id, $user->visibleUserIds(), true);
    }
}
