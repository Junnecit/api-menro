<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportFolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'agency_id' => $this->agency_id,
            'item_count' => ($this->children_count ?? 0) + ($this->files_count ?? 0),
            'folder_count' => $this->children_count ?? null,
            'file_count' => $this->files_count ?? null,
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
