<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
}
