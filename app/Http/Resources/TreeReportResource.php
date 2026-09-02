<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreeReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_uuid' => $this->client_uuid,
            'report_code' => $this->report_code,
            'tree_id' => $this->tree_id,
            'request_id' => $this->request_id,
            'agency_id' => $this->agency_id,
            'reported_by_id' => $this->reported_by_id,
            'report_type' => $this->report_type?->value ?? (string) $this->report_type,
            'report_type_label' => $this->report_type?->label() ?? (string) $this->report_type,
            'severity' => $this->severity?->value ?? (string) $this->severity,
            'severity_label' => $this->severity?->label() ?? (string) $this->severity,
            'tree_status_update' => $this->tree_status_update?->value ?? (string) $this->tree_status_update,
            'status' => $this->status?->value ?? (string) $this->status,
            'status_label' => $this->status?->label() ?? (string) $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'action_taken' => $this->action_taken,
            'barangay' => $this->barangay,
            'municipality' => $this->municipality,
            'province' => $this->province,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'landmark' => $this->landmark,
            'resolved_by_id' => $this->resolved_by_id,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolution_notes' => $this->resolution_notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'tree' => new TreeResource($this->whenLoaded('tree')),
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'reported_by' => new UserResource($this->whenLoaded('reportedBy')),
            'resolved_by' => new UserResource($this->whenLoaded('resolvedBy')),
        ];
    }
}
