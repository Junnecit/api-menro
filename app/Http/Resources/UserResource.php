<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => new RoleResource($this->whenLoaded('role')),
            'admin_id' => $this->admin_id,
            'admin' => new UserResource($this->whenLoaded('admin')),
            'agency_id' => $this->agency_id,
            // Field users inherit agency from their managing admin.
            'effective_agency_id' => $this->effectiveAgencyId(),
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'status' => $this->status?->value ?? $this->status,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'address' => $this->address,
            'profile_photo_url' => \App\Support\SignedMediaUrl::profilePhoto($this->resource),
            'push_enabled' => (bool) ($this->push_enabled ?? true),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
