<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantRoleName;
use Spatie\Permission\Models\Role;

test('user can create a tenant', function () {
    $user = User::factory()->create(['max_tenants' => 5]);

    $response = $this->actingAs($user)->post(route('tenants.store'), [
        'name' => 'Test Tenant',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    
    $this->assertDatabaseHas('tenants', [
        'name' => 'Test Tenant',
    ]);

    expect($user->tenants()->count())->toBe(1);
    
    // Verify role was assigned
    $tenant = Tenant::where('name', 'Test Tenant')->first();
    setPermissionsTeamId($tenant->id);
    expect($user->hasRole(TenantRoleName::Admin->value))->toBeTrue();
});

test('user cannot create more tenants than their limit', function () {
    $user = User::factory()->create(['max_tenants' => 1]);

    // Create the first tenant
    $this->actingAs($user)->post(route('tenants.store'), [
        'name' => 'First Tenant',
    ]);

    // Attempt to create a second tenant
    $response = $this->actingAs($user)->post(route('tenants.store'), [
        'name' => 'Second Tenant',
    ]);

    $response->assertForbidden(); // Should be blocked by authorize() in StoreTenantRequest
    
    expect($user->tenants()->count())->toBe(1);
    
    $this->assertDatabaseMissing('tenants', [
        'name' => 'Second Tenant',
    ]);
});

test('user can update a tenant they belong to and have permissions for', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'Old Name']);
    $user->tenants()->attach($tenant->id);
    
    // Assign permission using the role seeded by our Seeder
    setPermissionsTeamId($tenant->id);
    $user->assignRole(TenantRoleName::Admin->value);

    $response = $this->actingAs($user)->put(route('tenants.update', $tenant), [
        'name' => 'New Name',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'New Name',
    ]);
});

test('user cannot update a tenant they do not have permissions for', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'Old Name']);
    // user is attached but has no permissions
    $user->tenants()->attach($tenant->id);

    $response = $this->actingAs($user)->put(route('tenants.update', $tenant), [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('tenants', [
        'id' => $tenant->id,
        'name' => 'Hacked Name',
    ]);
});

test('user can delete a tenant they have DeleteTenant permission for', function () {
    // Need to pre-confirm password for the delete route
    $this->withSession(['auth.password_confirmed_at' => time()]);
    
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'To Be Deleted']);
    $user->tenants()->attach($tenant->id);
    
    // Assign role seeded by our Seeder
    setPermissionsTeamId($tenant->id);
    $user->assignRole(\App\Enums\TenantRoleName::Admin->value);

    $response = $this->actingAs($user)->delete(route('tenants.destroy', $tenant));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('tenants', [
        'id' => $tenant->id,
    ]);
});

test('user cannot delete a tenant they do not have DeleteTenant permission for', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['name' => 'Cannot Delete Me']);
    $user->tenants()->attach($tenant->id);
    
    // User is attached but does not have the DeleteTenant permission

    $response = $this->actingAs($user)->delete(route('tenants.destroy', $tenant));

    $response->assertForbidden();

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
    ]);
});
