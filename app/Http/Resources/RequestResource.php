<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $documentUrl = $this->document_path
            ? $request->getSchemeAndHttpHost().'/storage/'.$this->document_path
            : null;

        return [
            'id' => $this->id,
            'request_no' => $this->request_no,
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'agency_id' => $this->agency_id,
            'requester_name' => $this->requester_name,
            'barangay_code' => $this->barangay_code,
            'location' => $this->location,
            'custom_address' => $this->custom_address,
            'document_url' => $documentUrl,
            'document_name' => $this->document_name,
            'document_mime' => $this->document_mime,
            'status' => $this->status,
            'request_date' => $this->request_date?->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
