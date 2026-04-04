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
     * @throws \App\Exceptions\LastAdminSafeGuardException if the user is the last admin (handled by RemoveTenantMemberAction)
     * @throws \App\Exceptions\TenantMemberNotFoundException if the user is not a member of the tenant (handled by RemoveTenantMemberAction)
     */
    public function __invoke(User $user, Tenant $tenant): void
    {
        ($this->removeMemberAction)($tenant, $user);
    }
}
