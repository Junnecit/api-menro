<?php

namespace App\Http\Requests\PlantingRequest;

use App\Enums\PlantingHabitat;
use App\Support\PlantingRequestUniqueness;
use App\Support\TagoloanLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePlantingRequestRequest extends FormRequest
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
        return [
            'document' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png,webp',
                'max:10240',
            ],
            'request_no' => ['nullable', 'string', 'max:50', 'unique:requests,request_no'],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'habitat' => ['nullable', Rule::in(PlantingHabitat::values())],
            'seedling_type' => ['required', 'string', 'max:500'],
            'target_trees' => ['required', 'integer', 'min:1'],
            'barangay_code' => ['required', 'string', Rule::in(TagoloanLocation::barangayCodes())],
            'custom_address' => ['nullable', 'string', 'max:1000'],
            'location' => ['prohibited'],
            // Status is optional: admins submit without one (forced to Pending
            // server-side) and only Super Admins may choose it on create.
            'status' => ['nullable', Rule::in([
                'Pending',
                'Approved',
                'Completed',
                'Rejected',
                'In Progress',
            ])],
            'request_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            PlantingRequestUniqueness::validateProjectName($validator);
            PlantingRequestUniqueness::validateDocument($validator);
        });
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Please upload a planting request document or clear photo of the form.',
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
