<?php

namespace App\Models\Domain\Resources;

use App\Models\Domain\Resources\Contracts\OrganisationResource;
use App\Models\Domain\OrganisationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerExample extends Model implements OrganisationResource
{
    use HasUuids;

    protected $fillable = [
        'organisation_unit_id',
        'type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'notes',
        'created_by',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function organisationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganisationUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
