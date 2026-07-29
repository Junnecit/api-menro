<?php

namespace App\Http\Requests\PlantingRequest;

use App\Support\TagoloanLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlantingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
            'request_no' => ['nullable', 'string', 'max:50', 'unique:requests,request_no'],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'barangay_code' => ['nullable', 'string', Rule::in(TagoloanLocation::barangayCodes())],
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

    public function messages(): array
    {
        return [
            'document.required' => 'Please upload a planting request document (PDF, DOC, or DOCX).',
            'document.mimes' => 'The document must be a PDF, DOC, or DOCX file.',
            'document.max' => 'The document may not be greater than 10 MB.',
        ];
    }
}
