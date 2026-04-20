<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Http\Request;

class LastAdminSafeGuardException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('tenant.last_admin_safeguard'));
    }
}
