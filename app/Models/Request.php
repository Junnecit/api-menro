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
     * Limit the query to rows owned by the given user, unless they are a Super
     * Admin — Super Admins see every account's requests.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
