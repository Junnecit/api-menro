<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'initials' => ['sometimes', 'required', 'string', 'max:5'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['nullable', Rule::in([
                'Government Agency',
                'Local Government',
                'Private Individual',
                'Cooperative',
                'NGO',
            ])],
            'contact' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'region_code' => ['sometimes', 'required', 'string', 'regex:/^\d{9}$/'],
            'province_code' => ['nullable', 'string', 'regex:/^\d{9}$/'],
            'municipality_code' => ['sometimes', 'required', 'string', 'regex:/^\d{9}$/'],
            'barangay_code' => ['sometimes', 'required', 'string', 'regex:/^\d{9}$/'],
            'region_name' => ['required_with:barangay_code', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'municipality_name' => ['required_with:barangay_code', 'string', 'max:255'],
            'barangay_name' => ['required_with:barangay_code', 'string', 'max:255'],
            'custom_address' => ['nullable', 'string', 'max:1000'],
            'location' => ['prohibited'],
            'soil_type' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
