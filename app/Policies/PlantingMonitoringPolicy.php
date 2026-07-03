<?php

namespace App\Policies;

use App\Models\PlantingMonitoring;
use App\Models\User;

class PlantingMonitoringPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return true;
    }

    public function delete(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return true;
    }

    public function restore(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return true;
    }

    public function forceDelete(User $user, PlantingMonitoring $plantingMonitoring): bool
    {
        return true;
    }
}
