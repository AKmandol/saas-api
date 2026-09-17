<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Acme Technologies',
                'slug' => 'acme-technologies',
                'email' => 'admin@acme.test',
                'phone' => '+8801700000001',
                'status' => 'active',
            ],

            [
                'name' => 'Global Solutions Ltd',
                'slug' => 'global-solutions',
                'email' => 'admin@global.test',
                'phone' => '+8801700000002',
                'status' => 'active',
            ],

            [
                'name' => 'Startup Labs',
                'slug' => 'startup-labs',
                'email' => 'admin@startup.test',
                'phone' => '+8801700000003',
                'status' => 'active',
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['slug' => $company['slug']],
                $company
            );
        }
    }
}
