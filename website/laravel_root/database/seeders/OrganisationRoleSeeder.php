<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class OrganisationRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Owner' => 'Full access to all organisation resources and settings.',
            'Admin' => 'Full access to manage nodes, users, and resources.',
            'Member' => 'Standard access to nodes and resources.',
            'Viewer' => 'Read-only access to nodes and resources.',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(['name' => $name], [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'description' => $description,
                'guard_name' => 'web'
            ]);
        }

        // Example permissions
        $permissions = [
            'resource.read',
            'resource.create',
            'resource.update',
            'resource.delete',
            'resource.share',
            'node.manage',
            'user.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name], [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'guard_name' => 'web'
            ]);
        }

        // Assign all permissions to Owner/Admin
        $owner = Role::where('name', 'Owner')->first();
        $admin = Role::where('name', 'Admin')->first();
        $allPermissions = Permission::all();

        $owner->permissions()->sync($allPermissions);
        $admin->permissions()->sync($allPermissions);
    }
}
