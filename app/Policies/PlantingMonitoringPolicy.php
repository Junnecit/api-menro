<?php

namespace App\Policies;

use App\Models\PlantingMonitoring;
use App\Models\User;

class PlantingMonitoringPolicy
{
    // The Report Center module is not available to the admin role.
    public function viewAny(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function view(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return ! $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function update(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return ! $user->isAdmin();
    }

    public function delete(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return ! $user->isAdmin();
    }

    public function restore(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return ! $user->isAdmin();
    }

    public function forceDelete(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return ! $user->isAdmin();
    }
}
