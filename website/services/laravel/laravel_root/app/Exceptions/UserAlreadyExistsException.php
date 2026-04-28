<?php

namespace App\Exceptions;

use DomainException;

class UserAlreadyExistsException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('auth.user_already_exists'));
    }
}
