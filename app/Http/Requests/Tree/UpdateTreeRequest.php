<?php

namespace App\Http\Requests\Tree;

use App\Enums\TreeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'species' => ['sometimes', 'required', 'string', 'max:255'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::enum(TreeStatus::class)],
            'date_planted' => ['nullable', 'date'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'inspector_id' => ['nullable', 'exists:users,id'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
