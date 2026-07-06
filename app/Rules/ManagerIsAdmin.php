<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensures a user's assigned manager (admin_id) points to an Admin account.
 * Regular users and super admins cannot manage other users.
 */
class ManagerIsAdmin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! User::whereKey($value)->first()?->isAdmin()) {
            $fail('The selected manager must be an admin.');
        }
    }
}
