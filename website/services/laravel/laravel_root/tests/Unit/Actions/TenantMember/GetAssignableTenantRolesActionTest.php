<?php

namespace Tests\Unit\Actions\TenantMember;

use App\Actions\TenantMember\GetAssignableTenantRolesAction;
use App\Enums\TenantRoleName;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\TenantPermissionName;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(GetAssignableTenantRolesAction::class);
    $this->tenant = Tenant::factory()->create();
    
    // Ensure standard tenant roles exist
    foreach (TenantRoleName::cases() as $role) {
        Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'tenant']);
    }

    // Ensure standard tenant permissions exist
    foreach (TenantPermissionName::cases() as $permission) {
        Permission::firstOrCreate(['name' => $permission->value, 'guard_name' => 'tenant']);
    }
});

test('admins can manage all tenant roles', function () {
    $admin = User::factory()->create();
    $admin->tenants()->attach($this->tenant->id);
    $admin->assignTenantRole($this->tenant, TenantRoleName::Admin);
    $admin->assignTenantPermission($this->tenant, TenantPermissionName::ManageTenantMemberRoles);

    $assignableRoles = ($this->action)($admin, $this->tenant);

    expect($assignableRoles)->toHaveCount(count(TenantRoleName::cases()));
    expect($assignableRoles)->toContain(TenantRoleName::Admin->value);
    expect($assignableRoles)->toContain(TenantRoleName::Manager->value);
});

test('non-admins can manage all roles except admin', function () {
    $manager = User::factory()->create();
    $manager->tenants()->attach($this->tenant->id);
    $manager->assignTenantRole($this->tenant, TenantRoleName::Manager);
    $manager->assignTenantPermission($this->tenant, TenantPermissionName::ManageTenantMemberRoles);

    $assignableRoles = ($this->action)($manager, $this->tenant);

    expect($assignableRoles)->not->toContain(TenantRoleName::Admin->value);
    expect($assignableRoles)->toContain(TenantRoleName::Manager->value);
    expect($assignableRoles)->toContain(TenantRoleName::Support->value);
});

test('users with no role can manage all roles except admin (for visibility rank checks)', function () {
    $user = User::factory()->create();
    $user->tenants()->attach($this->tenant->id);
    $user->assignTenantPermission($this->tenant, TenantPermissionName::ManageTenantMemberRoles);

    $assignableRoles = ($this->action)($user, $this->tenant);

    expect($assignableRoles)->not->toContain(TenantRoleName::Admin->value);
    expect($assignableRoles)->toContain(TenantRoleName::Manager->value);
});

test('returns empty array if user lacks ManageTenantMemberRoles permission', function () {
    $user = User::factory()->create();
    $user->tenants()->attach($this->tenant->id);
    $user->assignTenantRole($this->tenant, TenantRoleName::Admin);
    // Specifically NOT assigning permission

    $assignableRoles = ($this->action)($user, $this->tenant);

    expect($assignableRoles)->toBeEmpty();
});
