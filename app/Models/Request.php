<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'request_no',
        'agency_id',
        'requester_name',
        'barangay_code',
        'location',
        'custom_address',
        'status',
        'request_date'
    ];

    protected $casts = [
        'request_date' => 'date'
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Limit the query to rows the given user owns. Super Admins see every
     * account's requests; an admin sees their own plus all of their managed
     * users'; a regular user sees only their own.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('user_id', $user->visibleUserIds());
    }
}
