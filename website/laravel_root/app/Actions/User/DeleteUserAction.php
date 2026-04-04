<?php

namespace App\Actions\User;

use App\Actions\Tenant\DeleteTenantAction;
use App\Actions\TenantMember\RemoveTenantMemberAction;
use App\Exceptions\LastAdminSafeGuardException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    public function __construct(
        protected DeleteTenantAction $deleteTenantAction,
        protected RemoveTenantMemberAction $removeMemberAction
    ) {}

    /**
     * Delete a user account, ensuring they are removed from all tenants.
     *
     * @param User $user
     * @param bool $forceDeleteTenants If true, deletes any tenant where the user is the last admin
     *                                 instead of failing the entire account deletion.
     * @throws LastAdminSafeGuardException if the user is the last admin of a tenant and $forceDeleteTenants is false
     */
    public function __invoke(User $user, bool $forceDeleteTenants = false): void
    {
        DB::transaction(function () use ($user, $forceDeleteTenants) {
            // Iterate over all tenants the user belongs to
            foreach ($user->tenants()->get() as $tenant) {
                try {
                    ($this->removeMemberAction)($tenant, $user);
                } catch (LastAdminSafeGuardException $e) {
                    if ($forceDeleteTenants) {
                        // If the user requested to force delete, silently delete the orphaned tenant
                        ($this->deleteTenantAction)($tenant);
                    } else {
                        // Otherwise, rethrow to abort the transaction and block account deletion
                        throw $e;
                    }
                }
            }

            // Finally, delete the user account
            $user->delete();
        });
    }
}
