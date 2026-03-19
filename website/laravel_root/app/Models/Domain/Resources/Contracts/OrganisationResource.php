<?php

namespace App\Models\Domain\Resources\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 */
interface OrganisationResource
{
    /**
     * Get the organisation unit that owns the resource.
     *
     * @return BelongsTo<\App\Models\Domain\OrganisationUnit, $this>
     * @phpstan-ignore generics.notSubtype
     */
    public function organisationUnit(): BelongsTo;
}
