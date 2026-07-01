<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = [
        'initials',
        'name',
        'soil_type',
        'color'
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
