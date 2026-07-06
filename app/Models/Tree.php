<?php

namespace App\Models;

use App\Enums\TreeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tree extends Model
{
    protected $fillable = [
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
     * Limit the query to the trees the given user owns. Super Admins see every
     * tree; an admin sees trees recorded by themselves plus their managed
     * users; a regular (mobile) user sees only the trees they recorded.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('recorded_by_id', $user->visibleUserIds());
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
