<?php

namespace App\Http\Requests\Auth;

use App\Rules\ManagerIsAdmin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            // Mobile (field-user) registration passes the chosen managing
            // admin's id here; web self-registration omits it entirely and
            // becomes an Admin account instead (see AuthController::register).
            'admin_id' => ['nullable', 'integer', 'exists:users,id', new ManagerIsAdmin],
        ];
    }
}
