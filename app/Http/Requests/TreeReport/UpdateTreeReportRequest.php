<?php

namespace App\Http\Requests\TreeReport;

use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Enums\TreeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTreeReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tree_id' => ['nullable', 'integer', 'exists:trees,id'],
            'request_id' => ['nullable', 'integer', 'exists:requests,id'],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'report_type' => ['sometimes', 'required', 'string', Rule::enum(ReportType::class)],
            'severity' => ['sometimes', 'required', 'string', Rule::enum(ReportSeverity::class)],
            'status' => ['sometimes', 'required', 'string', Rule::enum(ReportStatus::class)],
            'tree_status_update' => ['nullable', 'string', Rule::enum(TreeStatus::class)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'action_taken' => ['nullable', 'string', 'max:2000'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
