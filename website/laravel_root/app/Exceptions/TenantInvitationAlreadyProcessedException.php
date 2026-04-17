<?php

namespace App\Exceptions;

use DomainException;

class TenantInvitationAlreadyProcessedException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('invitations.already_processed'));
    }
}
