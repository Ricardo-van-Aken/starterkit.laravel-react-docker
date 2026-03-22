<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use Spatie\Permission\Models\Role;

test('user can create a tenant', function () {
    /* --- Setup --- */
    $user = User::factory()->create(['max_tenants' => 5]);

    /* --- Request --- */
    $response = $this->actingAs($user)->post(route('tenants.store'), [
        'name' => 'Test Tenant',
    ]);

    /* --- Assertions --- */
    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $response->assertSessionHas('status', __('tenant.created'));
    
    // Verify Database Insert
    $this->assertDatabaseHas('tenants', [
        'name' => 'Test Tenant',
    ]);

    $tenant = Tenant::where('name', 'Test Tenant')->first();
    
    // Verify Pivot Attachment
    expect($tenant->users->contains($user))->toBeTrue();
    
    // Verify 'Admin' Role Assignment
    expect($user->hasTenantRole($tenant, TenantRoleName::Admin))->toBeTrue();
});

test('user cannot create more tenants than their limit', function () {
    /* --- Setup --- */
    $user = User::factory()->create(['max_tenants' => 1]);
    $tenant = Tenant::factory()->create(['name' => 'First Tenant']);
    $user->tenants()->attach($tenant->id);

    /* --- Request --- */
    $response = $this->actingAs($user)->post(route('tenants.store'), [
        'name' => 'Second Tenant',
    ]);

    /* --- Assertions --- */
    $response->assertForbidden(); // Should be blocked by authorize() in StoreTenantRequest
    
    // Verify Database
    expect($user->tenants()->count())->toBe(1);
    $this->assertDatabaseMissing('tenants', [
        'name' => 'Second Tenant',
    ]);
});

test('user can update a tenant they belong to and have permissions for', function () {
    /* --- Setup --- */
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'Old Name']);
    $user->tenants()->attach($tenant->id);
    
    // Assign permission using the role seeded by our Seeder
    setPermissionsTeamId($tenant->id);
    $user->givePermissionTo(\App\Models\Permission::findByName(TenantPermissionName::UpdateTenantDetails->value, 'tenant'));

    /* --- Request --- */
    $response = $this->actingAs($user)->put(route('tenants.update', $tenant), [
        'name' => 'New Name',
    ]);

    /* --- Assertions --- */
    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'New Name',
    ]);
});

test('user cannot update a tenant they do not have permissions for', function () {
    /* --- Setup --- */
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'Old Name']);
    // user is attached but has no permissions
    $user->tenants()->attach($tenant->id);

    /* --- Request --- */
    $response = $this->actingAs($user)->put(route('tenants.update', $tenant), [
        'name' => 'New Name',
    ]);

    /* --- Assertions --- */
    $response->assertForbidden();
    $this->assertDatabaseMissing('tenants', [
        'id' => $tenant->id,
        'name' => 'New Name',
    ]);
});

test('user can delete a tenant they have DeleteTenant permission for', function () {
    /* --- Setup --- */
    // Need to pre-confirm password for the delete route
    $this->withSession(['auth.password_confirmed_at' => time()]);
    
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'To Be Deleted']);
    $user->tenants()->attach($tenant->id);
    
    // Assign role seeded by our Seeder
    $user->assignTenantRole($tenant, TenantRoleName::Admin);

    /* --- Request --- */
    $response = $this->actingAs($user)->delete(route('tenants.destroy', $tenant));

    /* --- Assertions --- */
    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('tenants', [
        'id' => $tenant->id,
    ]);
});

test('user cannot delete a tenant they do not have DeleteTenant permission for', function () {
    /* --- Setup --- */
    // Pre-confirm password so we truly test the Authorization Policy, not the Password Middleware
    $this->withSession(['auth.password_confirmed_at' => time()]);

    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'Cannot Delete Me']);
    $user->tenants()->attach($tenant->id);
    
    // User is attached but does not have the DeleteTenant permission

    /* --- Request --- */
    $response = $this->actingAs($user)->delete(route('tenants.destroy', $tenant));

    /* --- Assertions --- */
    $response->assertForbidden();

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
    ]);
});

test('user cannot update a tenant they belong to if they only have Admin permissions in a different tenant', function () {
    /* --- Setup --- */
    $user = User::factory()->create();
    
    // Create a primary tenant and make the user an Admin of it
    $userTenant = Tenant::factory()->create(['name' => 'Users Own Tenant']);
    $user->tenants()->attach($userTenant->id);
    $user->assignTenantRole($userTenant, TenantRoleName::Admin);

    // Create a separate tenant that the user IS attached to, but has NO role in
    $otherTenant = Tenant::factory()->create(['name' => 'Other Persons Tenant']);
    $user->tenants()->attach($otherTenant->id);

    /* --- Request --- */
    // The user attempts to update the isolated tenant
    $response = $this->actingAs($user)->put(route('tenants.update', $otherTenant), [
        'name' => 'New Name',
    ]);

    /* --- Assertions --- */
    $response->assertForbidden();

    $this->assertDatabaseHas('tenants', [
        'id' => $otherTenant->id,
        'name' => 'Other Persons Tenant', // Verify original name remains untouched
    ]);
});
