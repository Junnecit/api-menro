<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedGoogleEmailException extends Exception
{
    public function __construct(string $email)
    {
        parent::__construct("The email \"{$email}\" is not authorized to sign in with Google.");
    }
}
