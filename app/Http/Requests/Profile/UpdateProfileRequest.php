<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($this->user()->id)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'address' => ['nullable', 'string', 'max:500'],
            // Agency fields (if user is linked to an agency)
            'agency_name' => ['nullable', 'string', 'max:255'],
            'initials' => ['nullable', 'string', 'max:10'],
            'type' => ['nullable', Rule::in([
                'Government Agency',
                'Local Government',
                'Private Individual',
                'Cooperative',
                'NGO',
            ])],
            'contact' => ['nullable', 'string', 'max:255'],
            'agency_email' => ['nullable', 'email', 'max:255'],
            'agency_phone' => ['nullable', 'string', 'max:50'],
            'region_code' => ['nullable', 'string'],
            'province_code' => ['nullable', 'string'],
            'municipality_code' => ['nullable', 'string'],
            'barangay_code' => ['nullable', 'string'],
            'region_name' => ['nullable', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'municipality_name' => ['nullable', 'string', 'max:255'],
            'barangay_name' => ['nullable', 'string', 'max:255'],
            'custom_address' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This name is already being used.',
        ];
    }
}
