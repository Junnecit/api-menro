<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = [
        'initials',
        'name',
        'type',
        'contact',
        'email',
        'phone',
        'location',
        'soil_type',
        'color',
        'status',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }
}
