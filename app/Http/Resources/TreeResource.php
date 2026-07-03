<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tree_code' => $this->tree_code,
            'species' => $this->species,
            'common_name' => $this->common_name,
            'status' => $this->status?->value ?? $this->status,
            'date_planted' => $this->date_planted?->toDateString(),
            'date_recorded' => $this->date_recorded?->toDateString(),
            'barangay' => $this->barangay,
            'municipality' => $this->municipality,
            'province' => $this->province,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'landmark' => $this->landmark,
            'notes' => $this->notes,
            'inspector' => new UserResource($this->whenLoaded('inspector')),
            'inspector_id' => $this->inspector_id,
            'recorded_by' => new UserResource($this->whenLoaded('recordedBy')),
            'recorded_by_id' => $this->recorded_by_id,
            'updated_by' => new UserResource($this->whenLoaded('updatedBy')),
            'updated_by_id' => $this->updated_by_id,
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'agency_id' => $this->agency_id,
            'photos' => TreePhotoResource::collection($this->whenLoaded('photos')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
