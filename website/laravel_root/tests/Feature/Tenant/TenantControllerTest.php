<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

/*
|--------------------------------------------------------------------------
| Tenant Creation
|--------------------------------------------------------------------------
*/
describe('Tenant Creation', function () {
    test('user can create a tenant', function () {
        /* --- Setup --- */
        $this->user->update(['max_tenants' => 5]);

        /* --- Request --- */
        $response = $this->from(route('tenants.index'))
            ->post(route('tenants.store'), [
                'name' => 'Test Tenant',
            ]);

        /* --- Assert HTTP response status --- */
        $response->assertStatus(302)->assertRedirect(route('tenants.index'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('tenant.created'));
        
        /* --- Assert DB State --- */
        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Tenant',
        ]);

        $tenant = Tenant::where('name', 'Test Tenant')->first();
        
        // Verify Pivot Attachment
        expect($tenant->users->contains($this->user))->toBeTrue();
        
        // Verify 'Admin' Role Assignment
        expect($this->user->fresh()->hasTenantRole($tenant, TenantRoleName::Admin))->toBeTrue();
    });

    test('user cannot create more tenants than their limit', function () {
        /* --- Setup --- */
        $this->user->update(['max_tenants' => 1]);
        $tenant = Tenant::factory()->create(['name' => 'First Tenant']);
        $this->user->tenants()->attach($tenant->id);

        /* --- Request --- */
        $response = $this->post(route('tenants.store'), [
            'name' => 'Second Tenant',
        ]);

        /* --- Assert HTTP response status --- */
        $response->assertStatus(302);
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error')[0])->toBe(__('tenant.status.limit_reached'));
        
        /* --- Assert DB State --- */
        expect($this->user->tenants()->count())->toBe(1);
        $this->assertDatabaseMissing('tenants', [
            'name' => 'Second Tenant',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Update & Deletion Helper Setup
|--------------------------------------------------------------------------
*/
describe('Tenant Management Actions', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->user->tenants()->attach($this->tenant->id);
        $this->withSession(['active_tenant_uuid' => $this->tenant->uuid]);
    });

    /* --- Update --- */
    describe('Update', function () {
        test('user can update a tenant they belong to and have permissions for', function () {
            /* --- Setup --- */
            $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);

            /* --- Request --- */
            $response = $this->from(route('tenants.index'))
                ->put(route('tenant.update'), [
                    'name' => 'New Name',
                ]);

            /* --- Assert HTTP response status --- */
            $response->assertStatus(302)->assertRedirect(route('tenants.index'));
            
            /* --- Assert HTTP response message/error --- */
            expect(session('status'))->toBe(__('tenant.updated'));
            
            /* --- Assert DB State --- */
            $this->assertDatabaseHas('tenants', [
                'id' => $this->tenant->id,
                'name' => 'New Name',
            ]);
        });

        test('user cannot update a tenant they belong to but do not have permissions for', function () {
            /* --- Request --- */
            $response = $this->put(route('tenant.update'), [
                'name' => 'New Name',
            ]);

            /* --- Assert HTTP response status --- */
            expect($response->status())->toBe(302);
            expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));
            
            /* --- Assert DB State --- */
            $this->assertDatabaseMissing('tenants', [
                'id' => $this->tenant->id,
                'name' => 'New Name',
            ]);
        });
    });

    /* --- Deletion --- */
    describe('Deletion', function () {
        beforeEach(function () {
            $this->withSession(['auth.password_confirmed_at' => time()]);
        });

        test('user can delete a tenant they have DeleteTenant permission for', function () {
            /* --- Setup --- */
            $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);

            /* --- Request --- */
            $response = $this->delete(route('tenant.destroy'));

            /* --- Assert HTTP response status --- */
            $response->assertStatus(302)->assertRedirect(route('tenants.index'));
            
            /* --- Assert HTTP response message/error --- */
            expect(session('status'))->toBe(__('tenant.deleted'));

            // Side Effects (DB state)
            $this->assertDatabaseMissing('tenants', [
                'id' => $this->tenant->id,
            ]);
        });

        test('user cannot delete a tenant they do not have DeleteTenant permission for', function () {
            /* --- Request --- */
            $response = $this->delete(route('tenant.destroy'));

            /* --- Assert HTTP response status --- */
            expect($response->status())->toBe(302);

            /* --- Assert HTTP response message/error --- */
            expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

            /* --- Assert DB State --- */
            $this->assertDatabaseHas('tenants', [
                'id' => $this->tenant->id,
            ]);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Contextual Authorization
|--------------------------------------------------------------------------
*/
describe('Tenant Contextual Authorization', function () {
    test('user cannot update a tenant they belong to if they only have Admin permissions in a different tenant', function () {
        /* --- Setup --- */
        // Create a primary tenant and make the user an Admin of it
        $userTenant = Tenant::factory()->create(['name' => 'Users Own Tenant']);
        $this->user->tenants()->attach($userTenant->id);
        $this->user->assignTenantRole($userTenant, TenantRoleName::Admin);

        // Create a separate tenant that the user IS attached to, but has NO role in
        $otherTenant = Tenant::factory()->create(['name' => 'Other Persons Tenant']);
        $this->user->tenants()->attach($otherTenant->id);

        /* --- Request --- */
        $response = $this->withSession(['active_tenant_uuid' => $otherTenant->uuid])
            ->put(route('tenant.update'), [
                'name' => 'New Name',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));
        
        /* --- Assert DB State --- */
        $this->assertDatabaseHas('tenants', [
            'id' => $otherTenant->id,
            'name' => 'Other Persons Tenant', 
        ]);
    });
});
