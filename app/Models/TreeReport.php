<?php

namespace App\Models;

use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Enums\TreeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreeReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_uuid',
        'report_code',
        'tree_id',
        'request_id',
        'agency_id',
        'reported_by_id',
        'report_type',
        'severity',
        'tree_status_update',
        'status',
        'title',
        'description',
        'action_taken',
        'barangay',
        'municipality',
        'province',
        'latitude',
        'longitude',
        'landmark',
        'resolved_by_id',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'report_type' => ReportType::class,
            'severity' => ReportSeverity::class,
            'status' => ReportStatus::class,
            'tree_status_update' => TreeStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function plantingRequest(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }

    /**
     * Limit the query to the reports the given user may see:
     * - Super Admins see every report.
     * - Planters see only the reports they submitted themselves.
     * - Admin/Monitors see reports filed within their agency pool or tagged with their agency.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isPlanter()) {
            return $query->where('reported_by_id', $user->id);
        }

        $poolIds = $user->agencyPoolUserIds();
        $agencyId = $user->effectiveAgencyId();

        if ($agencyId || $user->isAdmin() || $user->admin_id) {
            return $query->where(function ($q) use ($poolIds, $agencyId) {
                $q->whereIn('reported_by_id', $poolIds);

                if ($agencyId) {
                    $q->orWhere('agency_id', $agencyId);
                }
            });
        }

        return $query->whereIn('reported_by_id', $poolIds);
    }

    public function scopeStatus($query, ?string $status)
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeSeverity($query, ?string $severity)
    {
        if (! $severity) {
            return $query;
        }

        return $query->where('severity', $severity);
    }

    public function scopeReportType($query, ?string $type)
    {
        if (! $type) {
            return $query;
        }

        return $query->where('report_type', $type);
    }

    public function scopeAgency($query, ?int $agencyId)
    {
        if (! $agencyId) {
            return $query;
        }

        return $query->where('agency_id', $agencyId);
    }

    public function scopeBarangay($query, ?string $barangay)
    {
        if (! $barangay) {
            return $query;
        }

        return $query->where('barangay', $barangay);
    }
}
