<?php

namespace App\Http\Requests\Auth;

use App\Rules\ManagerIsAdmin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            // Mobile (field-user) registration passes the chosen managing
            // admin's id here; web self-registration omits it and becomes an
            // Admin account with a linked agency (see AuthController::register).
            'admin_id' => ['nullable', 'integer', 'exists:users,id', new ManagerIsAdmin],
        ];

        if ($this->filled('admin_id')) {
            // Mobile field-user signup collects personal contact details.
            return array_merge($rules, [
                'phone' => ['required', 'string', 'max:50'],
                'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            ]);
        }

        // Web admin registration only requires basic credentials; agency details are optional and can be completed on Profile.
        return array_merge($rules, [
            'initials' => ['nullable', 'string', 'max:10'],
            'agency_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in([
                'Government Agency',
                'Local Government',
                'Private Individual',
                'Cooperative',
                'NGO',
            ])],
            'contact' => ['nullable', 'string', 'max:255'],
            'agency_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'region_code' => ['nullable', 'string'],
            'province_code' => ['nullable', 'string'],
            'municipality_code' => ['nullable', 'string'],
            'barangay_code' => ['nullable', 'string'],
            'region_name' => ['nullable', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'municipality_name' => ['nullable', 'string', 'max:255'],
            'barangay_name' => ['nullable', 'string', 'max:255'],
            'custom_address' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This name is already being used.',
        ];
    }
}
