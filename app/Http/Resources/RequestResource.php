<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_no' => $this->request_no,
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'agency_id' => $this->agency_id,
            'requester_name' => $this->requester_name,
            'location' => $this->location,
            'status' => $this->status,
            'request_date' => $this->request_date?->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
