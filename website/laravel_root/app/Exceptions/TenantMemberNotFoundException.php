<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Http\Request;

class TenantMemberNotFoundException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('tenant.not_a_member'));
    }
}
