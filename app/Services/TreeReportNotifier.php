<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\TreeReport;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TreeReportNotifier
{
    /**
     * Notify agency admin(s) and super-admins when a new field report is created.
     */
    public function notifyAdminsOfNewReport(TreeReport $report, User $reporter): void
    {
        $adminQuery = User::query()->whereHas('role', function ($q) {
            $q->whereIn('slug', ['admin', 'super-admin']);
        });

        // If report is tagged with an agency, find admins of that agency + all super-admins
        if ($report->agency_id) {
            $adminQuery->where(function ($q) use ($report) {
                $q->where('agency_id', $report->agency_id)
                    ->orWhereHas('role', fn ($rq) => $rq->where('slug', 'super-admin'));
            });
        }

        $admins = $adminQuery->get();

        $typeLabel = $report->report_type?->label() ?? 'Field Incident';
        $severityLabel = strtoupper($report->severity?->value ?? 'MEDIUM');
        $title = "New {$severityLabel} Report: {$report->title}";
        $body = "{$reporter->name} submitted a {$typeLabel} report at {$report->barangay}, {$report->municipality}.";

        foreach ($admins as $admin) {
            if ($admin->id === $reporter->id) {
                continue;
            }

            $notification = AppNotification::create([
                'user_id' => $admin->id,
                'type' => 'tree_report_created',
                'title' => $title,
                'body' => $body,
                'data' => [
                    'report_id' => $report->id,
                    'report_code' => $report->report_code,
                    'tree_id' => $report->tree_id,
                    'severity' => $report->severity?->value,
                    'status' => $report->status?->value,
                    'reported_by_id' => $reporter->id,
                ],
            ]);

            $this->sendExpoPush($admin, $notification);
        }
    }

    /**
     * Notify the reporter when their report is reviewed, resolved, or updated.
     */
    public function notifyReporterOfStatusUpdate(TreeReport $report, User $actor): void
    {
        $reporterId = $report->reported_by_id;
        if (! $reporterId || $reporterId === $actor->id) {
            return;
        }

        $reporter = User::query()->find($reporterId);
        if (! $reporter) {
            return;
        }

        $statusLabel = $report->status?->label() ?? (string) $report->status;
        $title = "Report #{$report->report_code} Updated";
        $body = "Your report '{$report->title}' status changed to '{$statusLabel}' by {$actor->name}.";

        if (! empty($report->resolution_notes)) {
            $body .= " Note: " . \Illuminate\Support\Str::limit($report->resolution_notes, 80);
        }

        $notification = AppNotification::create([
            'user_id' => $reporter->id,
            'type' => 'tree_report_updated',
            'title' => $title,
            'body' => $body,
            'data' => [
                'report_id' => $report->id,
                'report_code' => $report->report_code,
                'tree_id' => $report->tree_id,
                'status' => $report->status?->value,
                'updated_by_id' => $actor->id,
            ],
        ]);

        $this->sendExpoPush($reporter, $notification);
    }

    private function sendExpoPush(User $user, AppNotification $notification): void
    {
        if (! $user->push_enabled || blank($user->expo_push_token)) {
            return;
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->post('https://exp.host/--/api/v2/push/send', [
                    'to' => $user->expo_push_token,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'sound' => 'default',
                    'data' => array_merge($notification->data ?? [], [
                        'notification_id' => $notification->id,
                        'type' => $notification->type,
                    ]),
                ]);

            if (! $response->successful()) {
                Log::warning('Expo push failed for tree report', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push exception for tree report', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
