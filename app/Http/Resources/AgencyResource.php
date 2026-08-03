<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $soilDocumentUrl = $this->soil_document_path
            ? url('/api/agencies/'.$this->id.'/soil-document/download')
            : null;

        return [
            'id' => $this->id,
            'initials' => $this->initials,
            'name' => $this->name,
            'type' => $this->type,
            'contact' => $this->contact,
            'email' => $this->email,
            'phone' => $this->phone,
            'region_code' => $this->region_code,
            'province_code' => $this->province_code,
            'municipality_code' => $this->municipality_code,
            'barangay_code' => $this->barangay_code,
            'location' => $this->location,
            'custom_address' => $this->custom_address,
            'soil_type' => $this->soil_type,
            'soil_document_url' => $soilDocumentUrl,
            'soil_document_name' => $this->soil_document_name,
            'soil_document_mime' => $this->soil_document_mime,
            'color' => $this->color,
            'status' => $this->status,
            'requests_count' => $this->whenCounted('requests'),
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
