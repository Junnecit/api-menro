<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'admin_id',
        'agency_id',
        'name',
        'email',
        'password',
        'status',
        'phone',
        'address',
        'profile_photo_path',
        'google_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function testItems(): HasMany
    {
        return $this->hasMany(TestItem::class);
    }

    /**
     * The admin who manages this user.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * The users this admin manages (and whose records they own).
     */
    public function managedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'admin_id');
    }

    /**
     * The stakeholder agency this admin account represents. Only set for
     * `admin` role accounts; null for super-admins and regular users.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * The set of user ids whose records this user owns. An admin owns their own
     * records plus every managed user's; everyone else owns only their own.
     * Super Admins bypass ownership entirely, so this is not used for them.
     */
    public function visibleUserIds(): array
    {
        if ($this->isAdmin()) {
            return $this->managedUsers()->pluck('id')->push($this->id)->all();
        }

        return [$this->id];
    }

    /**
     * Whether this user may see/act on the given account. Super Admins see
     * everyone; an admin sees themselves and the users they manage; a regular
     * user sees only themselves. Checks admin_id directly so it holds for
     * soft-deleted (trashed) accounts too.
     */
    public function canManageUser(User $model): bool
    {
        if ($this->isSuperAdmin() || $model->id === $this->id) {
            return true;
        }

        return $this->isAdmin() && $model->admin_id === $this->id;
    }

    /**
     * Limit a user query to the accounts the given user may see: all for a
     * Super Admin; self + managed users for an admin; self only otherwise.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('admin_id', $user->id)->orWhere('id', $user->id);
            });
        }

        return $query->where('id', $user->id);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super-admin';
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isAdminOrAbove(): bool
    {
        return $this->role?->isAdminOrAbove() ?? false;
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeRoleSlug($query, ?string $roleSlug)
    {
        if (! $roleSlug) {
            return $query;
        }

        return $query->whereHas('role', fn ($q) => $q->where('slug', $roleSlug));
    }

    public function scopeStatus($query, ?string $status)
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
