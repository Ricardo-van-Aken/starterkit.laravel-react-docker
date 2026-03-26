<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantRoleName;
use App\Enums\TenantPermissionName;
use Database\Seeders\DefaultRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $managerUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(DefaultRolesAndPermissionsSeeder::class);

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create Admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->tenants()->attach($this->tenant->id);
        $this->adminUser->assignTenantRole($this->tenant, TenantRoleName::Admin);

        // Create Manager user (has UpdateTenantMembers permission but is NOT an Admin role)
        $this->managerUser = User::factory()->create();
        $this->managerUser->tenants()->attach($this->tenant->id);
        $this->managerUser->assignTenantRole($this->tenant, TenantRoleName::Manager);

        // Create Regular user
        $this->regularUser = User::factory()->create();
        $this->regularUser->tenants()->attach($this->tenant->id);
    }

    /** @test */
    public function non_admin_cannot_delete_an_admin()
    {
        $response = $this->actingAs($this->managerUser)
            ->delete(route('tenant-members.destroy', $this->adminUser));

        $response->assertForbidden();
        $this->assertTrue($this->tenant->users()->where('users.id', $this->adminUser->id)->exists());
    }

    /** @test */
    public function admin_can_delete_another_admin()
    {
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->tenants()->attach($this->tenant->id);
        $anotherAdmin->assignTenantRole($this->tenant, TenantRoleName::Admin);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('tenant-members.destroy', $anotherAdmin));

        $response->assertRedirect();
        $this->assertFalse($this->tenant->users()->where('users.id', $anotherAdmin->id)->exists());
    }

    /** @test */
    public function non_admin_cannot_update_an_admin()
    {
        $response = $this->actingAs($this->managerUser)
            ->put(route('tenant-members.update', $this->adminUser), [
                'roles' => [TenantRoleName::Manager->value],
                'permissions' => [],
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_update_another_admin()
    {
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->tenants()->attach($this->tenant->id);
        $anotherAdmin->assignTenantRole($this->tenant, TenantRoleName::Admin);

        $response = $this->actingAs($this->adminUser)
            ->put(route('tenant-members.update', $anotherAdmin), [
                'roles' => [TenantRoleName::Manager->value],
                'permissions' => [],
            ]);

        $response->assertRedirect();
        $this->assertTrue($anotherAdmin->hasTenantRole($this->tenant, TenantRoleName::Manager));
        $this->assertFalse($anotherAdmin->hasTenantRole($this->tenant, TenantRoleName::Admin));
    }

    /** @test */
    public function non_admin_cannot_assign_the_admin_role()
    {
        $response = $this->actingAs($this->managerUser)
            ->put(route('tenant-members.update', $this->regularUser), [
                'roles' => [TenantRoleName::Admin->value],
                'permissions' => [],
            ]);

        $response->assertForbidden();
        $this->assertFalse($this->regularUser->hasTenantRole($this->tenant, TenantRoleName::Admin));
    }

    /** @test */
    public function admin_can_assign_the_admin_role()
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('tenant-members.update', $this->regularUser), [
                'roles' => [TenantRoleName::Admin->value],
                'permissions' => [],
            ]);

        $response->assertRedirect();
        $this->assertTrue($this->regularUser->hasTenantRole($this->tenant, TenantRoleName::Admin));
    }

    /** @test */
    public function member_can_leave_a_tenant()
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('tenants.leave', $this->tenant));

        $response->assertRedirect(route('dashboard'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->regularUser->id)->exists());
    }

    /** @test */
    public function last_admin_cannot_leave_a_tenant()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('tenants.leave', $this->tenant));

        $response->assertSessionHasErrors('error');
        $this->assertTrue($this->tenant->users()->where('users.id', $this->adminUser->id)->exists());
    }

    /** @test */
    public function admin_can_leave_if_not_the_last_admin()
    {
        // Add another admin
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->tenants()->attach($this->tenant->id);
        $anotherAdmin->assignTenantRole($this->tenant, TenantRoleName::Admin);

        $response = $this->actingAs($this->adminUser)
            ->post(route('tenants.leave', $this->tenant));

        $response->assertRedirect(route('dashboard'));
        $this->assertFalse($this->tenant->users()->where('users.id', $this->adminUser->id)->exists());
    }
}
