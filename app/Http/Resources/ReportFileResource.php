<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            'name' => $this->name,
            'mime' => $this->mime,
            'size' => $this->size,
            'source' => $this->source,
            'source_key' => $this->source_key,
            'url' => $this->path
                ? $request->getSchemeAndHttpHost().'/storage/'.$this->path
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
