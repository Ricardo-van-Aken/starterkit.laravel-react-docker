<?php

namespace App\Exceptions;

use DomainException;

class TenantInvitationAlreadyExistsException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('invitations.already_exists'));
    }
}
