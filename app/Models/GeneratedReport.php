<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedReport extends Model
{
    protected $fillable = [
        'user_id',
        'agency_id',
        'report_type',
        'title',
        'filename',
        'file_path',
        'file_size',
        'record_count',
        'filters',
        'generated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'file_size' => 'integer',
        'record_count' => 'integer',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Scope query to only reports visible to the given user.
     * Super Admin sees all; Admin sees agency pool; Planters see their own.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin() && $user->agency_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('agency_id', $user->agency_id)
                    ->orWhere('user_id', $user->id);
            });
        }

        return $query->where('user_id', $user->id);
    }
}
