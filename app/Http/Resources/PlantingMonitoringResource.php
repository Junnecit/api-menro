<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantingMonitoringResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $seedlingsPlanted = (int) $this->seedlings_planted;
        $survived = (int) $this->survived_count;

        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'request' => new RequestResource($this->whenLoaded('request')),
            'seedling_type' => $this->seedling_type,
            'date_monitoring' => $this->date_monitoring?->toDateString(),
            'seedlings_planted' => $seedlingsPlanted,
            'replanted_count' => (int) $this->replanted_count,
            'survived_count' => $survived,
            'died_count' => (int) $this->died_count,
            'survival_rate' => $seedlingsPlanted > 0
                ? round($survived / $seedlingsPlanted * 100, 2)
                : 0.0,
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
