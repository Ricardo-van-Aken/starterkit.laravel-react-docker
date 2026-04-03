<?php

namespace App\Actions\User;

use App\Enums\TenantRoleName;
use App\Exceptions\LastAdminSafeGuardException;
use App\Models\User;

class ScheduleUserDeletionAction
{
    /**
     * Pre-flight checks and schedules a user for deletion in 30 days.
     *
     * @throws LastAdminSafeGuardException
     */
    public function handle(User $user, bool $forceDeleteTenants): void
    {
        // Don't allow scheduling if it will force an orphaned tenant, unless they explicitly opted in
        if (! $forceDeleteTenants) {
            foreach ($user->tenants()->get() as $tenant) {
                if ($user->hasTenantRole($tenant, TenantRoleName::Admin)) {
                    if ($tenant->activeAdminsCount($user) === 0) {
                        throw new LastAdminSafeGuardException();
                    }
                }
            }
        }

        $user->forceFill([
            'scheduled_for_deletion_at' => now()->addDays(30),
        ])->save();
    }
}
