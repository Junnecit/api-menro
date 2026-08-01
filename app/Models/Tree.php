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

    /**
     * Limit the query to the trees the given user may see. Super Admins see
     * every tree. An admin (and their managed field users) see trees recorded
     * in their agency pool, plus every tree tagged with that agency — keeping
     * each agency partitioned from the others'. Unassigned
     * regular users see only the trees they recorded.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $poolIds = $user->agencyPoolUserIds();
        $agencyId = $user->effectiveAgencyId();

        if ($agencyId || $user->isAdmin() || $user->admin_id) {
            return $query->where(function ($q) use ($poolIds, $agencyId) {
                $q->whereIn('recorded_by_id', $poolIds);

                if ($agencyId) {
                    $q->orWhere('agency_id', $agencyId);
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
