<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Http\Request;

class TenantInvitationExpiredException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('invitations.expired'));
    }
}
