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
