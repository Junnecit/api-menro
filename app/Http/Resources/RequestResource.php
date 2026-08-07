<?php

namespace App\Http\Resources;

use App\Support\TagoloanLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $documentUrl = $this->document_path
            ? url('/api/requests/'.$this->id.'/document/download')
            : null;

        $barangayName = $this->barangay_code
            ? TagoloanLocation::barangayName($this->barangay_code)
            : null;

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'request_no' => $this->request_no,
            'user_id' => $this->user_id,
            'submitted_by' => $this->whenLoaded('user', function () {
                if (! $this->user) {
                    return null;
                }

                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'agency_id' => $this->agency_id,
            'requester_name' => $this->requester_name,
            'project_name' => $this->project_name,
            'habitat' => $this->habitat?->value ?? 'terrestrial',
            'target_trees' => $this->target_trees,
            'barangay_code' => $this->barangay_code,
            'barangay_name' => $barangayName,
            'municipality' => 'Tagoloan',
            'province' => 'Misamis Oriental',
            'location' => $this->location,
            'custom_address' => $this->custom_address,
            'document_url' => $documentUrl,
            'document_name' => $this->document_name,
            'document_mime' => $this->document_mime,
            'seedling_draft' => $this->seedling_draft,
            'status' => $this->status,
            'request_date' => $this->request_date?->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            'children_count' => $this->relationLoaded('children')
                ? $this->children->count()
                : $this->whenCounted('children'),
            'children' => RequestResource::collection($this->whenLoaded('children')),
        ];
    }
}
