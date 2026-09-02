<?php

namespace App\Policies;

use App\Models\TreeReport;
use App\Models\User;

class TreeReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TreeReport $report): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($report->reported_by_id === $user->id) {
            return true;
        }

        $pool = $user->agencyPoolUserIds();
        $agencyId = $user->effectiveAgencyId();

        if (in_array($report->reported_by_id, $pool, true)) {
            return true;
        }

        if ($agencyId && $report->agency_id === $agencyId) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TreeReport $report): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin() || $user->isMonitor()) {
            $pool = $user->agencyPoolUserIds();
            $agencyId = $user->effectiveAgencyId();

            return in_array($report->reported_by_id, $pool, true)
                || ($agencyId && $report->agency_id === $agencyId);
        }

        // Planters can only edit their own reports while in 'submitted' status
        if ($user->isPlanter() && $report->reported_by_id === $user->id) {
            return $report->status?->value === 'submitted' || (string) $report->status === 'submitted';
        }

        return false;
    }

    public function delete(User $user, TreeReport $report): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            $pool = $user->agencyPoolUserIds();
            $agencyId = $user->effectiveAgencyId();

            return in_array($report->reported_by_id, $pool, true)
                || ($agencyId && $report->agency_id === $agencyId);
        }

        return false;
    }

    public function restore(User $user, TreeReport $report): bool
    {
        return $this->delete($user, $report);
    }

    public function forceDelete(User $user, TreeReport $report): bool
    {
        return $user->isSuperAdmin();
    }
}
