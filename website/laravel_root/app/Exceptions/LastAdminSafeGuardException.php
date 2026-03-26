<?php

namespace App\Exceptions;

use Exception;

class LastAdminSafeGuardException extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('tenant.last_admin_safeguard'));
    }
}
