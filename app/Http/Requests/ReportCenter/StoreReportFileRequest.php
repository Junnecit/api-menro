<?php

namespace App\Http\Requests\ReportCenter;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,webp,txt',
                'max:20480',
            ],
            'folder_id' => ['nullable', 'integer', 'exists:report_folders,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please choose a file to upload.',
            'file.mimes' => 'Allowed types: PDF, DOC, DOCX, XLS, XLSX, CSV, images, or TXT.',
            'file.max' => 'The file may not be greater than 20 MB.',
        ];
    }
}
