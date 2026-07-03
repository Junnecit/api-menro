<?php

namespace App\Http\Requests\PlantingMonitoring;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlantingMonitoringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $monitoring = $this->route('planting_monitoring');

        return [
            'request_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:requests,id',
                Rule::unique('planting_monitorings', 'request_id')
                    ->where(fn ($query) => $query->whereNull('deleted_at'))
                    ->ignore($monitoring?->id),
            ],
            'seedling_type' => ['sometimes', 'required', 'string', 'max:255'],
            'date_monitoring' => ['nullable', 'date'],
            'seedlings_planted' => ['nullable', 'integer', 'min:0'],
            'replanted_count' => ['nullable', 'integer', 'min:0'],
            'survived_count' => ['nullable', 'integer', 'min:0'],
            'died_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'request_id.unique' => 'This planting request already has a monitoring record.',
        ];
    }
}
