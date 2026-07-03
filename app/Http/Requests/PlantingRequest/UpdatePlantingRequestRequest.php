<?php

namespace App\Http\Requests\PlantingRequest;

use App\Support\TagoloanLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlantingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $plantingRequest = $this->route('request');

        return [
            'request_no' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('requests', 'request_no')->ignore($plantingRequest?->id),
            ],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'barangay_code' => ['sometimes', 'required', 'string', Rule::in(TagoloanLocation::barangayCodes())],
            'custom_address' => ['nullable', 'string', 'max:1000'],
            'location' => ['prohibited'],
            'status' => ['sometimes', 'required', Rule::in([
                'Pending',
                'Approved',
                'Completed',
                'Rejected',
                'In Progress',
            ])],
            'request_date' => ['sometimes', 'required', 'date'],
        ];
    }
}
