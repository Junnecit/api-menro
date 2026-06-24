<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'request_no',
        'agency_id',
        'requester_name',
        'location',
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
}
