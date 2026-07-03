<?php

namespace App\Models;

use App\Models\Request as PlantingRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantingMonitoring extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_id',
        'seedling_type',
        'date_monitoring',
        'seedlings_planted',
        'replanted_count',
        'survived_count',
        'died_count',
    ];

    protected $casts = [
        'date_monitoring' => 'date',
        'seedlings_planted' => 'integer',
        'replanted_count' => 'integer',
        'survived_count' => 'integer',
        'died_count' => 'integer',
    ];

    public function request()
    {
        return $this->belongsTo(PlantingRequest::class, 'request_id');
    }
}
