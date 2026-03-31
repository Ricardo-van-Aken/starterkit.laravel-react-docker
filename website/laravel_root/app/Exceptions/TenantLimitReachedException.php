<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Http\Request;

class TenantLimitReachedException extends DomainException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: __('tenant.status.limit_reached'));
    }

    public function render(Request $request): never
    {
        back()->withErrors(['error' => $this->getMessage()])->throwResponse();
    }
}
