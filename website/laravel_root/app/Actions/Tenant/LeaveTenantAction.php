<?php

namespace App\Actions\Tenant;

use App\Actions\TenantMember\RemoveTenantMemberAction;
use App\Models\Tenant;
use App\Models\User;

class LeaveTenantAction
{
    public function __construct(
        protected RemoveTenantMemberAction $removeMemberAction
    ) {}

    /**
     * Execute the action to allow a user to leave a tenant.
     *
     * @throws \RuntimeException if the user is the last admin (handled by RemoveTenantMemberAction)
     */
    public function handle(User $user, Tenant $tenant): void
    {
        $this->removeMemberAction->handle($tenant, $user);
    }
}
