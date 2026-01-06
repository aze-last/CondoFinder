<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $owner = Role::firstOrCreate(['name' => 'Owner']);

        // Create Permissions (optional, can just use roles for now)
        Permission::firstOrCreate(['name' => 'manage listings']);
        Permission::firstOrCreate(['name' => 'manage categories']);
        Permission::firstOrCreate(['name' => 'manage users']);

        // Assign Permissions
        $owner->givePermissionTo(['manage listings', 'manage categories']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}
