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
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlantingRequest $plantingRequest): bool
    {
        return true;
    }

    public function delete(User $user, PlantingRequest $plantingRequest): bool
    {
        return true;
    }

    public function restore(User $user, PlantingRequest $plantingRequest): bool
    {
        return true;
    }

    public function forceDelete(User $user, PlantingRequest $plantingRequest): bool
    {
        return true;
    }
}
