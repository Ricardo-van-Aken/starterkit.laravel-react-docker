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

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): never
    {
        // When encountered during an HTTP request, gracefully return the user 
        // to their previous page with an error banner rather than a hard 404.
        back()->withErrors(['error' => $this->getMessage()])->throwResponse();
    }
}
