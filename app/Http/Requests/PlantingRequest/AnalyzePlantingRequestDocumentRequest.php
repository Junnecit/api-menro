<?php

namespace App\Http\Requests\PlantingRequest;

use App\Support\PlantingRequestUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AnalyzePlantingRequestDocumentRequest extends FormRequest
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
                'mimes:pdf,doc,docx,jpg,jpeg,png,webp',
                'max:10240',
            ],
            // When replacing a document on edit, ignore the current request's hash.
            'ignore_request_id' => ['nullable', 'integer', 'exists:requests,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $ignoreId = $this->filled('ignore_request_id')
            ? $this->integer('ignore_request_id')
            : null;

        $validator->after(function (Validator $validator) use ($ignoreId) {
            PlantingRequestUniqueness::validateDocument($validator, 'document', $ignoreId);
        });
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Please choose a planting request document or photo.',
            'document.mimes' => 'The file must be a PDF, DOC, DOCX, JPG, PNG, or WEBP.',
            'document.max' => 'The file may not be greater than 10 MB.',
        ];
    }
}
