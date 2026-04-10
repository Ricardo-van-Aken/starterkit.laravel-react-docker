<?php

namespace Database\Seeders;

use App\Enums\TenantInvitationStatus;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\AccountInvitation;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::transaction(function () {
            // 1. Create 50 Users
            $users = User::factory()->count(50)->withoutTwoFactor()->create();

            // 2. Tenant 1 Setup
            $tenant1 = Tenant::factory()->create(['name' => 'Tenant One (Multi-Admin)']);

            $admin1 = $users[0];
            $admin1->tenants()->attach($tenant1->id);
            $admin1->assignTenantRole($tenant1, TenantRoleName::Admin);

            // One Admin pending for deletion
            $admin2 = $users[1];
            $admin2->tenants()->attach($tenant1->id);
            $admin2->assignTenantRole($tenant1, TenantRoleName::Admin);
            $admin2->forceFill(['scheduled_for_deletion_at' => now()->addYears(10)])->save();

            // Another Admin
            $admin3 = $users[2];
            $admin3->tenants()->attach($tenant1->id);
            $admin3->assignTenantRole($tenant1, TenantRoleName::Admin);

            // Varied members for Tenant 1 (using indices 3-15)
            for ($i = 0; $i < 13; $i++) {
                $user = $users[3 + $i];
                $user->tenants()->attach($tenant1->id);
                $this->assignVariedToModel($user, $tenant1, $i);
            }

            // 15 Outstanding invitations to Tenant 1 with varied statuses
            $statusOptions = TenantInvitationStatus::cases();

            // 5 to existing users (indices 16-20)
            for ($i = 0; $i < 5; $i++) {
                $user = $users[16 + $i];
                $invitation = TenantInvitation::factory()->create([
                    'tenant_id' => $tenant1->id,
                    'email' => $user->email,
                    'status' => $statusOptions[$i % count($statusOptions)],
                ]);

                $this->assignVariedToModel($invitation, $tenant1, $i);
            }

            // 10 to account invites (new users)
            for ($i = 0; $i < 10; $i++) {
                $email = fake()->unique()->safeEmail();
                
                AccountInvitation::factory()->create([
                    'email' => $email,
                ]);

                $invitation = TenantInvitation::factory()->create([
                    'tenant_id' => $tenant1->id,
                    'email' => $email,
                    'status' => $statusOptions[$i % count($statusOptions)],
                ]);

                $this->assignVariedToModel($invitation, $tenant1, $i);
            }

            // 3. Tenant 2 Setup
            $tenant2 = Tenant::factory()->create(['name' => 'Tenant Two (Solo Admin)']);
            $admin1->tenants()->attach($tenant2->id);
            $admin1->assignTenantRole($tenant2, TenantRoleName::Admin);

            // 4. Five Independent Tenants with Invitations to Admin 1
            for ($i = 0; $i < 5; $i++) {
                $tenant = Tenant::factory()->create(['name' => "Independent Tenant " . ($i + 1)]);
                
                // New admin for this tenant (users index 30 to 34)
                $externalAdmin = $users[30 + $i];
                $externalAdmin->tenants()->attach($tenant->id);
                $externalAdmin->assignTenantRole($tenant, TenantRoleName::Admin);

                // Invitation to Admin 1 - now with combination of roles and permissions
                $invitation = TenantInvitation::factory()->create([
                    'tenant_id' => $tenant->id,
                    'email' => $admin1->email,
                    'status' => TenantInvitationStatus::Pending,
                ]);

                // Always give both a role and a permission to Admin 1's invitations
                $invitation->assignTenantRole($tenant, TenantRoleName::Manager);
                $invitation->assignTenantPermission($tenant, TenantPermissionName::ViewBillingInformation);
            }
        });
    }

    /**
     * Helper to assign varied roles and permissions to a model (User or Invitation).
     *
     * @param \App\Models\User|\App\Models\TenantInvitation $model
     * @param \App\Models\Tenant $tenant
     * @param int $index
     * @return void
     */
    protected function assignVariedToModel(User|TenantInvitation $model, Tenant $tenant, int $index): void
    {
        switch ($index % 5) {
            case 0:
                // Multiple roles
                $model->assignTenantRole($tenant, TenantRoleName::Manager);
                $model->assignTenantRole($tenant, TenantRoleName::Finance);
                break;
            case 1:
                // No roles, multiple permissions
                $model->assignTenantPermission($tenant, TenantPermissionName::ViewTenantMembers);
                $model->assignTenantPermission($tenant, TenantPermissionName::ViewBillingInformation);
                break;
            case 2:
                // One role, one permission
                $model->assignTenantRole($tenant, TenantRoleName::Manager);
                $model->assignTenantPermission($tenant, TenantPermissionName::UpdateTenantDetails);
                break;
            case 3:
                // Multiple roles, multiple permissions
                $model->assignTenantRole($tenant, TenantRoleName::Manager);
                $model->assignTenantRole($tenant, TenantRoleName::Finance);
                $model->assignTenantPermission($tenant, TenantPermissionName::CreateOrganisationUnits);
                $model->assignTenantPermission($tenant, TenantPermissionName::ViewOrganisationUnits);
                break;
            default:
                // None
                break;
        }
    }
}
