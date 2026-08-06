<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === 'super-admin';
    }

    public function isAdmin(): bool
    {
        return $this->slug === 'admin';
    }

    public function isAdminOrAbove(): bool
    {
        return in_array($this->slug, ['super-admin', 'admin'], true);
    }

    public function isPlanter(): bool
    {
        return in_array($this->slug, ['user', 'other'], true);
    }

    public function isMonitor(): bool
    {
        return $this->slug === 'monitor';
    }

    /** Field roles that require a managing admin for agency-pool scoping. */
    public function needsManagingAdmin(): bool
    {
        return in_array($this->slug, ['user', 'other', 'monitor'], true);
    }
}
