<?php

namespace App\Http\Requests\PlantingRequest;

use App\Enums\PlantingHabitat;
use App\Support\PlantingRequestUniqueness;
use App\Support\TagoloanLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePlantingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('project_name') && is_string($this->input('project_name'))) {
            $this->merge(['project_name' => trim($this->input('project_name'))]);
        }
    }

    public function rules(): array
    {
        $plantingRequest = $this->route('request');

        return [
            'document' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png,webp',
                'max:10240',
            ],
            'request_no' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('requests', 'request_no')->ignore($plantingRequest?->id),
            ],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'project_name' => ['sometimes', 'required', 'string', 'max:255'],
            'habitat' => ['sometimes', 'nullable', Rule::in(PlantingHabitat::values())],
            'seedling_type' => ['sometimes', 'required', 'string', 'max:500'],
            'target_trees' => ['sometimes', 'required', 'integer', 'min:1'],
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

    public function withValidator(Validator $validator): void
    {
        $ignoreId = $this->route('request')?->id;

        $validator->after(function (Validator $validator) use ($ignoreId) {
            if ($this->exists('project_name')) {
                PlantingRequestUniqueness::validateProjectName($validator, $ignoreId);
            }

            if ($this->hasFile('document')) {
                PlantingRequestUniqueness::validateDocument($validator, 'document', $ignoreId);
            }
        });
    }

    public function messages(): array
    {
        return [
            'document.mimes' => 'The file must be a PDF, DOC, DOCX, JPG, PNG, or WEBP.',
            'document.max' => 'The file may not be greater than 10 MB.',
            'project_name.required' => 'Please enter a project name.',
            'seedling_type.required' => 'Please enter the type of seedling.',
            'target_trees.required' => 'Please enter the target number of trees.',
            'target_trees.min' => 'Target trees must be at least 1.',
            'barangay_code.required' => 'Please select a barangay.',
        ];
    }
}
