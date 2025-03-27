<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'create-product',
            'edit-product',
            'delete-product',
            'view-product',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'role-store',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users'
        ];

        // ✅ Step 1: Create permissions first
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ✅ Step 2: Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // ✅ Step 3: Assign permissions to roles (after permissions exist)
        $adminRole->syncPermissions($permissions); // Admin gets all permissions
        $userRole->syncPermissions(['view-users']); // User only gets view access
    }
}
