<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'initials',
        'name',
        'type',
        'contact',
        'email',
        'phone',
        'region_code',
        'province_code',
        'municipality_code',
        'barangay_code',
        'location',
        'custom_address',
        'soil_type',
        'color',
        'status',
    ];

    /**
     * The admin account that represents this agency in the portal.
     * At most one per agency (enforced by a unique constraint on users.agency_id).
     */
    public function admin()
    {
        return $this->hasOne(User::class, 'agency_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }
}
