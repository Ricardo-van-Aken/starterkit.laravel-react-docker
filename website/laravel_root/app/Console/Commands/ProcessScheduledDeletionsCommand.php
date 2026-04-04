<?php

namespace App\Console\Commands;

use App\Actions\User\DeleteUserAction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledDeletionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:process-scheduled-deletions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete user accounts that have surpassed their 30-day scheduled retention period.';

    /**
     * Execute the console command.
     */
    public function handle(DeleteUserAction $deleteUserAction): void
    {
        $this->info("Fetching users scheduled for deletion before: " . now()->toDateTimeString());

        // Find users strictly past their retention period
        $usersToPurge = User::whereNotNull('scheduled_for_deletion_at')
            ->where('scheduled_for_deletion_at', '<=', now())
            ->get();

        if ($usersToPurge->isEmpty()) {
            $this->info("No users scheduled for permanent deletion found today.");
            return;
        }

        $this->info("Found {$usersToPurge->count()} user(s) to permanently delete.");

        foreach ($usersToPurge as $user) {
            /** @var \App\Models\User $user */
            $this->info("Purging user: {$user->email} (UUID: {$user->uuid})");

            try {
                // We unconditionally force-purge associated tenants.
                // If a tenant still has other active admins, the action merely removes this user.
                // If this user was the last active admin, the tenant becomes completely orphaned, so we garbage collect it.
                $deleteUserAction($user, true);
                $this->info("Successfully purged: {$user->email}");
            } catch (\Exception $e) {
                // We catch general exceptions so one failing user doesn't prevent others from being purged
                $this->error("Failed to purge user {$user->email}: {$e->getMessage()}");
                Log::error("Scheduled deletion failure for {$user->email}", ['exception' => $e]);
            }
        }

        $this->info("Scheduled deletion process complete.");
    }
}
