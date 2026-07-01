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
            'soil_type' => $this->soil_type,
            'color' => $this->color,
        ];
    }
}
