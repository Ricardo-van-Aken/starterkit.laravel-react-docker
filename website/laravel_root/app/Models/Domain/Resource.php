<?php

namespace App\Models\Domain;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_unit_id',
        'type',
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
