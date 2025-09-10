<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Course permissions
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
            'publish courses',
            'view published courses', // Permission khusus untuk member
            
            // Module permissions
            'create modules',
            'edit modules',
            'delete modules',
            'reorder modules',
            
            // Lesson permissions
            'create lessons',
            'edit lessons',
            'delete lessons',
            'reorder lessons',
            'upload videos',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        $memberRole = Role::create(['name' => 'member']);
        $memberRole->givePermissionTo(['view published courses']);
    }
} 