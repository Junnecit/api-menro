<?php

namespace App\Models;

use App\Enums\PlantingHabitat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'user_id',
        'request_no',
        'agency_id',
        'requester_name',
        'project_name',
        'habitat',
        'target_trees',
        'barangay_code',
        'location',
        'custom_address',
        'document_path',
        'document_name',
        'document_mime',
        'document_hash',
        'seedling_draft',
        'status',
        'request_date',
    ];

    protected $casts = [
        'request_date' => 'date',
        'target_trees' => 'integer',
        'seedling_draft' => 'array',
        'habitat' => PlantingHabitat::class,
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function trees()
    {
        return $this->hasMany(Tree::class, 'request_id');
    }

    /**
     * Top-level list rows (parents and standalone singles).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Real planting rows — excludes empty shell parents (no document, no trees).
     */
    public function scopeOperational($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('document_path')
                ->orWhereHas('trees')
                ->orWhereDoesntHave('children');
        });
    }

    /**
     * True when this row only groups children and has no planting payload of its own.
     */
    public function isEmptyShell(): bool
    {
        $hasChildren = $this->relationLoaded('children')
            ? $this->children->isNotEmpty()
            : $this->children()->exists();

        if (! $hasChildren) {
            return false;
        }

        if ($this->document_path) {
            return false;
        }

        return ! $this->trees()->exists();
    }

    public function isParentShell(): bool
    {
        return $this->isEmptyShell();
    }

    /**
     * Statuses that allow mobile field users to register trees against the request.
     */
    public function isPlantable(): bool
    {
        return in_array($this->status, ['Approved', 'In Progress'], true);
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
