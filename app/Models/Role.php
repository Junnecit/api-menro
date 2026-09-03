<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function defaultPermissionsFor(string $slug): array
    {
        return match ($slug) {
            'super-admin' => ['overview', 'requests', 'map', 'agencies', 'reports', 'settings', 'users', 'permissions'],
            'admin' => ['overview', 'requests', 'map', 'agencies', 'reports', 'settings', 'users', 'permissions'],
            'user', 'other' => ['overview', 'requests', 'map', 'agencies', 'reports', 'settings'],
            'monitor' => ['overview', 'requests', 'map', 'agencies', 'reports', 'settings'],
            default => ['overview', 'requests', 'map', 'settings'],
        };
    }

    public function getEffectivePermissions(): array
    {
        return is_array($this->permissions)
            ? array_values(array_unique($this->permissions))
            : static::defaultPermissionsFor($this->slug);
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
