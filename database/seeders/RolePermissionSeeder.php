<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'company.view',
            'company.update',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            'subscription.view',
            'subscription.update',

            'analytics.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Owner
        |--------------------------------------------------------------------------
        */

        $owner = Role::firstOrCreate([
            'name' => 'owner',
            'guard_name' => 'web',
        ]);

        $owner->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions([
            'company.view',
            'company.update',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            'subscription.view',
            'analytics.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        $manager->syncPermissions([
            'company.view',

            'users.view',

            'customers.view',
            'customers.create',
            'customers.update',

            'subscription.view',

            'analytics.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        $user = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $user->syncPermissions([
            'customers.view',
            'customers.create',
        ]);
    }
}
