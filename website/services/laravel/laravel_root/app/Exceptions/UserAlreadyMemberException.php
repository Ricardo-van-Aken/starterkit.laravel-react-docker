<?php

namespace App\Exceptions;

use DomainException;

class UserAlreadyMemberException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('invitations.user_already_member'));
    }
}
