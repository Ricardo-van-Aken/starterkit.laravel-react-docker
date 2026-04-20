<?php

namespace Database\Seeders;

use App\Enums\TenantInvitationStatus;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\AccountInvitation;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Pre-fetch Roles and Permissions to avoid repeated queries
            /** @var Collection<string, Role> $roles */
            $roles = Role::where('guard_name', 'tenant')->get()->keyBy('name');
            /** @var Collection<string, Permission> $permissions */
            $permissions = Permission::where('guard_name', 'tenant')->get()->keyBy('name');

            // 2. Setup Users (1 Main + 150 Others)
            $allUsers = User::factory()->count(151)->withoutTwoFactor()->create();
            
            // Dynamic Demo Email logic
            $counter = 1;
            while (User::where('email', "demo{$counter}@example.com")->exists()) {
                $counter++;
            }
            $demoEmail = "demo{$counter}@example.com";

            /** @var User $mainUser */
            $mainUser = $allUsers[0];
            $mainUser->email = $demoEmail;
            $mainUser->save();

            $otherUsersPool = $allUsers->slice(1);
            $statusOptions = TenantInvitationStatus::cases();

            // 3. Batch Create 30 Tenants
            $tenants = Tenant::factory()->count(30)->sequence(fn (\Illuminate\Database\Eloquent\Factories\Sequence $sequence) => [
                'name' => match($sequence->index) {
                    0 => 'Tenant One (Main Admin)',
                    1 => 'Tenant Two (Solo Admin - One Deleting)',
                    default => "Independent Tenant " . ((int) $sequence->index + 1),
                }
            ])->create();

            // 4. Populate Tenants
            foreach ($tenants as $i => $tenant) {
                // --- Special Rules for T1 and T2 ---
                if ($i === 0) {
                    $mainUser->tenants()->attach($tenant->id);
                    /** @var Role $adminRole */
                    $adminRole = $roles[TenantRoleName::Admin->value];
                    $this->optimizedAssignRole($mainUser, $tenant, $adminRole);
                } elseif ($i === 1) {
                    $mainUser->tenants()->attach($tenant->id);
                    /** @var Role $adminRole */
                    $adminRole = $roles[TenantRoleName::Admin->value];
                    $this->optimizedAssignRole($mainUser, $tenant, $adminRole);
                    
                    $deletingAdmin = $otherUsersPool->random();
                    /** @var User $deletingAdmin */
                    $deletingAdmin->tenants()->attach($tenant->id);
                    $this->optimizedAssignRole($deletingAdmin, $tenant, $adminRole);
                    $deletingAdmin->forceFill(['scheduled_for_deletion_at' => now()->addDays(30)])->save();
                }

                // --- Memberships for Main User (5 Tenants between 2-6) ---
                if ($i >= 2 && $i <= 6) {
                    $mainUser->tenants()->attach($tenant->id);
                    $this->assignVariedToModel($mainUser, $tenant, $i, $roles, $permissions);
                }

                // --- Invitations for Main User (10 Tenants between 7-16) ---
                if ($i >= 7 && $i <= 16) {
                    $invitation = TenantInvitation::factory()->create([
                        'tenant_id' => $tenant->id,
                        'email' => $mainUser->email,
                        'status' => $statusOptions[$i % count($statusOptions)],
                    ]);
                    $this->assignVariedToModel($invitation, $tenant, $i, $roles, $permissions);
                }

                // --- Random Population for all Tenants ---
                // Add some random members (batch attach)
                $randomMembers = $otherUsersPool->random(random_int(2, 10));
                
                // For tenants excluding T1 & T2 (which already have mainUser as Admin), assign an explicit Admin
                if ($i >= 2) {
                    /** @var User $admin */
                    $admin = $randomMembers->shift();
                    $admin->tenants()->attach($tenant->id);
                    /** @var Role $adminRole */
                    $adminRole = $roles[TenantRoleName::Admin->value];
                    $this->optimizedAssignRole($admin, $tenant, $adminRole);
                }

                foreach ($randomMembers as $member) {
                    if ($member->tenants()->where('tenants.id', $tenant->id)->exists()) continue;
                    $member->tenants()->attach($tenant->id);
                    $this->assignVariedToModel($member, $tenant, random_int(0, 4), $roles, $permissions);
                }

                // Add some random invitations (batch create)
                $invitationCount = random_int(2, 10);
                $invitations = TenantInvitation::factory()->count($invitationCount)->create([
                    'tenant_id' => $tenant->id,
                    'status' => fn() => $statusOptions[random_int(0, count($statusOptions) - 1)],
                ]);

                foreach ($invitations as $inv) {
                    $this->assignVariedToModel($inv, $tenant, random_int(0, 4), $roles, $permissions);
                }
            }
        });
    }

    /**
     * Optimized role assignment to avoid repeated queries.
     */
    protected function optimizedAssignRole(User|TenantInvitation $model, Tenant $tenant, Role $role): void
    {
        setPermissionsTeamId($tenant->id);
        $model->assignRole($role);
    }

    /**
     * Optimized permission assignment to avoid repeated queries.
     */
    protected function optimizedAssignPermission(User|TenantInvitation $model, Tenant $tenant, Permission $permission): void
    {
        setPermissionsTeamId($tenant->id);
        $model->givePermissionTo($permission);
    }

    /**
     * Helper to assign varied roles and permissions using pre-fetched models.
     * 
     * @param Collection<string, Role> $roles 
     * @param Collection<string, Permission> $permissions
     */
    protected function assignVariedToModel(
        User|TenantInvitation $model, 
        Tenant $tenant, 
        int $index, 
        Collection $roles, 
        Collection $permissions
    ): void {
        switch ($index % 5) {
            case 0:
                /** @var Role $managerRole */
                $managerRole = $roles[TenantRoleName::Manager->value];
                $this->optimizedAssignRole($model, $tenant, $managerRole);
                break;
            case 1:
                /** @var Permission $viewMembersPermission */
                $viewMembersPermission = $permissions[TenantPermissionName::ViewTenantMembers->value];
                /** @var Permission $viewBillingPermission */
                $viewBillingPermission = $permissions[TenantPermissionName::ViewBillingInformation->value];
                $this->optimizedAssignPermission($model, $tenant, $viewMembersPermission);
                $this->optimizedAssignPermission($model, $tenant, $viewBillingPermission);
                break;
            case 2:
                /** @var Role $financeRole */
                $financeRole = $roles[TenantRoleName::Finance->value];
                /** @var Permission $updateDetailsPermission */
                $updateDetailsPermission = $permissions[TenantPermissionName::UpdateTenantDetails->value];
                $this->optimizedAssignRole($model, $tenant, $financeRole);
                $this->optimizedAssignPermission($model, $tenant, $updateDetailsPermission);
                break;
            case 3:
                /** @var Role $managerRole */
                $managerRole = $roles[TenantRoleName::Manager->value];
                /** @var Role $financeRole */
                $financeRole = $roles[TenantRoleName::Finance->value];
                /** @var Permission $createUnitsPermission */
                $createUnitsPermission = $permissions[TenantPermissionName::CreateOrganisationUnits->value];
                /** @var Permission $viewUnitsPermission */
                $viewUnitsPermission = $permissions[TenantPermissionName::ViewOrganisationUnits->value];
                
                $this->optimizedAssignRole($model, $tenant, $managerRole);
                $this->optimizedAssignRole($model, $tenant, $financeRole);
                $this->optimizedAssignPermission($model, $tenant, $createUnitsPermission);
                $this->optimizedAssignPermission($model, $tenant, $viewUnitsPermission);
                break;
            case 4:
                /** @var Permission $viewMembersPermission */
                $viewMembersPermission = $permissions[TenantPermissionName::ViewTenantMembers->value];
                $this->optimizedAssignPermission($model, $tenant, $viewMembersPermission);
                break;
        }
    }
}
