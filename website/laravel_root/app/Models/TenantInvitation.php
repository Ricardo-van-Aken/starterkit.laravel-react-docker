<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $email
 * @property \App\Enums\TenantInvitationStatus $status
 * @property string[]|null $roles
 * @property string[]|null $permissions
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \App\Models\Tenant $tenant
 */
class TenantInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\TenantInvitationFactory> */
    use HasUuids, HasFactory;

    protected $fillable = [
        'tenant_id',
        'email',
        'status',
        'roles',
        'permissions',
        'expires_at',
    ];

    protected $hidden = [
        'id',
        'accept_token',
        'decline_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\TenantInvitationStatus::class,
            'roles' => 'array',
            'permissions' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return array<int, string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }


    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
