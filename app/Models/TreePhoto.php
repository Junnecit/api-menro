<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreePhoto extends Model
{
    protected $fillable = [
        'tree_id',
        'path',
        'capture_mode',
        'angle',
    ];

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }
}
