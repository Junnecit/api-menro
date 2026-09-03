<?php

namespace App\Http\Requests\Tree;

use App\Enums\TreeStatus;
use App\Models\Request as PlantingRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Tree::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_uuid' => ['nullable', 'uuid'],
            'request_id' => ['required', 'integer', 'exists:requests,id'],
            'species' => ['required', 'string', 'max:255'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(TreeStatus::class)],
            'date_planted' => ['nullable', 'date'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'inspector_id' => ['nullable', 'exists:users,id'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'notes' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_capture_modes' => ['nullable', 'array', 'max:6'],
            'photo_capture_modes.*' => ['nullable', 'string', Rule::in(['single', '360'])],
            'photo_angles' => ['nullable', 'array', 'max:6'],
            'photo_angles.*' => ['nullable', 'string', Rule::in(['N', 'E', 'S', 'W', ''])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('request_id')) {
                return;
            }

            $plantingRequest = PlantingRequest::query()->find($this->integer('request_id'));

            if (! $plantingRequest) {
                return;
            }

            if (! $plantingRequest->isPlantable()) {
                $validator->errors()->add(
                    'request_id',
                    'Trees can only be registered against Approved or In Progress planting requests.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'request_id.required' => 'Please select an approved planting request.',
            'request_id.exists' => 'The selected planting request is invalid.',
        ];
    }
}
