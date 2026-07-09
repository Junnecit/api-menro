<?php

namespace App\Exceptions;

use App\Enums\UserStatus;
use Exception;

class InactiveGoogleAccountException extends Exception
{
    public function __construct(UserStatus $status)
    {
        $message = $status === UserStatus::Pending
            ? 'Your account is awaiting approval from your managing admin.'
            : 'Your account is not active.';

        parent::__construct($message);
    }
}
