<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folder_id',
        'name',
        'path',
        'mime',
        'size',
        'source',
        'source_key',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ReportFolder::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
