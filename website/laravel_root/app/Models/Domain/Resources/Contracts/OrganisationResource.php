<?php

namespace App\Models\Domain\Resources\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface OrganisationResource
{
    /**
     * Get the organisation unit that owns the resource.
     */
    public function organisationUnit(): BelongsTo;
}
