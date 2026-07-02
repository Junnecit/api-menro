<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'initials' => $this->initials,
            'name' => $this->name,
            'type' => $this->type,
            'contact' => $this->contact,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'soil_type' => $this->soil_type,
            'color' => $this->color,
            'status' => $this->status,
            'requests_count' => $this->whenCounted('requests'),
        ];
    }
}
