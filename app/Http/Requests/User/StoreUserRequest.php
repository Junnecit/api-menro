<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use App\Rules\ManagerIsAdmin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'admin_id' => ['nullable', 'integer', 'exists:users,id', new ManagerIsAdmin],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id', Rule::unique('users', 'agency_id')],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This name is already being used.',
        ];
    }
}
