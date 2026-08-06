<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
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
        'date_of_birth',
        'address',
        'profile_photo_path',
        'google_id',
        'email_verified_at',
        'expo_push_token',
        'push_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'expo_push_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'push_enabled' => 'boolean',
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
     * The agency this admin account represents. Only set for
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
     * User ids in this account's agency pool (for read/sync scopes).
     * Admins: self + managed users. Field users under an admin: that admin's
     * pool. Unassigned users: self only. Super Admins should not call this —
     * they bypass pool filters entirely.
     *
     * @return list<int>
     */
    public function agencyPoolUserIds(): array
    {
        if ($this->isAdmin()) {
            return $this->visibleUserIds();
        }

        if ($this->admin_id) {
            $admin = $this->relationLoaded('admin') ? $this->admin : $this->admin()->first();

            if ($admin) {
                return $admin->visibleUserIds();
            }
        }

        return [$this->id];
    }

    /**
     * Agency this account operates under. Admins carry agency_id
     * directly; managed field users inherit it from their admin.
     */
    public function effectiveAgencyId(): ?int
    {
        if ($this->agency_id) {
            return (int) $this->agency_id;
        }

        if ($this->admin_id) {
            $admin = $this->relationLoaded('admin') ? $this->admin : $this->admin()->first();

            return $admin?->agency_id ? (int) $admin->agency_id : null;
        }

        return null;
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
    public function scopeVisibleTo(Builder $query, User $user): Builder
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

    /** Field planter (`user`) or legacy `other` — may plant, not edit after upload. */
    public function isPlanter(): bool
    {
        return in_array($this->role?->slug, ['user', 'other'], true);
    }

    /** Field monitor — agency-pool edit, no tree create. */
    public function isMonitor(): bool
    {
        return $this->role?->slug === 'monitor';
    }

    /** Roles that belong under a managing admin (agency pool). */
    public function needsManagingAdmin(): bool
    {
        return in_array($this->role?->slug, ['user', 'other', 'monitor'], true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeRoleSlug(Builder $query, ?string $roleSlug): Builder
    {
        if (! $roleSlug) {
            return $query;
        }

        return $query->whereHas('role', fn ($q) => $q->where('slug', $roleSlug));
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
