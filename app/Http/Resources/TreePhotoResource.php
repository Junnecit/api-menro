<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreePhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $request->getSchemeAndHttpHost().'/storage/'.$this->path,
            'capture_mode' => $this->capture_mode,
            'angle' => $this->angle,
        ];
    }
}
