<?php

namespace App\Models;

use App\Enums\TreeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tree extends Model
{
    protected $fillable = [
        'request_id',
        'tree_code',
        'client_uuid',
        'species',
        'common_name',
        'status',
        'date_planted',
        'date_recorded',
        'barangay',
        'municipality',
        'province',
        'latitude',
        'longitude',
        'landmark',
        'inspector_id',
        'recorded_by_id',
        'updated_by_id',
        'agency_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'status' => TreeStatus::class,
            'date_planted' => 'date',
            'date_recorded' => 'date',
        ];
    }

    public function plantingRequest(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TreePhoto::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(TreeReport::class);
    }

    /**
     * Limit the query to the trees the given user may see. Super Admins see
     * every tree. Planters see only the trees they recorded themselves.
     * An admin (and their monitors) see trees recorded in their agency pool,
     * plus every tree tagged with that agency — keeping each agency
     * partitioned from the others'. Unassigned regular users see only
     * the trees they recorded.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Planters only see their own recorded trees
        if ($user->isPlanter()) {
            return $query->where('recorded_by_id', $user->id);
        }

        $poolIds = $user->agencyPoolUserIds();
        $agencyId = $user->effectiveAgencyId();

        if ($agencyId || $user->isAdminOrAbove() || $user->admin_id) {
            return $query->where(function ($q) use ($poolIds, $agencyId, $user) {
                $q->whereIn('recorded_by_id', $poolIds);

                if ($agencyId) {
                    $q->orWhere('agency_id', $agencyId);
                }

                // A monitor also sees all trees planted by their assigned admin or any planter under that admin
                if ($user->isMonitor() && $user->admin_id) {
                    $q->orWhere('recorded_by_id', $user->admin_id)
                      ->orWhereHas('recordedBy', function ($rq) use ($user) {
                          $rq->where('admin_id', $user->admin_id);
                      });
                }
            });
        }

        return $query->whereIn('recorded_by_id', $poolIds);
    }

    public function scopeStatus($query, ?string $status)
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeAgency($query, ?int $agencyId)
    {
        if (! $agencyId) {
            return $query;
        }

        return $query->where('agency_id', $agencyId);
    }

    public function scopeBarangay($query, ?string $barangay)
    {
        if (! $barangay) {
            return $query;
        }

        return $query->where('barangay', $barangay);
    }
}
