<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Company 1
        |--------------------------------------------------------------------------
        */

        $acme = Company::where('slug', 'acme-technologies')->firstOrFail();

        $owner = User::updateOrCreate(
            ['email' => 'owner@acme.test'],
            [
                'company_id' => $acme->id,
                'name' => 'Acme Owner',
                'password' => Hash::make('password'),
            ]
        );

        $owner->syncRoles(['owner']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@acme.test'],
            [
                'company_id' => $acme->id,
                'name' => 'Acme Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles(['admin']);

        $manager = User::updateOrCreate(
            ['email' => 'manager@acme.test'],
            [
                'company_id' => $acme->id,
                'name' => 'Acme Manager',
                'password' => Hash::make('password'),
            ]
        );

        $manager->syncRoles(['manager']);

        $user = User::updateOrCreate(
            ['email' => 'user@acme.test'],
            [
                'company_id' => $acme->id,
                'name' => 'Acme User',
                'password' => Hash::make('password'),
            ]
        );

        $user->syncRoles(['user']);

        /*
        |--------------------------------------------------------------------------
        | Company 2
        |--------------------------------------------------------------------------
        */

        $global = Company::where('slug', 'global-solutions')->firstOrFail();

        $owner = User::updateOrCreate(
            ['email' => 'owner@global.test'],
            [
                'company_id' => $global->id,
                'name' => 'Global Owner',
                'password' => Hash::make('password'),
            ]
        );

        $owner->syncRoles(['owner']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@global.test'],
            [
                'company_id' => $global->id,
                'name' => 'Global Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles(['admin']);

        /*
        |--------------------------------------------------------------------------
        | Company 3
        |--------------------------------------------------------------------------
        */

        $startup = Company::where('slug', 'startup-labs')->firstOrFail();

        $owner = User::updateOrCreate(
            ['email' => 'owner@startup.test'],
            [
                'company_id' => $startup->id,
                'name' => 'Startup Owner',
                'password' => Hash::make('password'),
            ]
        );

        $owner->syncRoles(['owner']);

        $user = User::updateOrCreate(
            ['email' => 'user@startup.test'],
            [
                'company_id' => $startup->id,
                'name' => 'Startup User',
                'password' => Hash::make('password'),
            ]
        );

        $user->syncRoles(['user']);
    }
}
