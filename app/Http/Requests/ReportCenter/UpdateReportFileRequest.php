<?php

namespace App\Http\Requests\ReportCenter;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:report_folders,id'],
        ];
    }
}
