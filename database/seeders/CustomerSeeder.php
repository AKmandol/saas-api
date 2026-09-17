<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {

            for ($i = 1; $i <= 10; $i++) {

                Customer::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'email' => "customer{$i}@{$company->slug}.test",
                    ],
                    [
                        'name' => "Customer {$i}",
                        'phone' => '+88018' . str_pad(
                            $company->id . $i,
                            8,
                            '0',
                            STR_PAD_LEFT
                        ),
                        'status' => $i % 5 === 0
                            ? 'inactive'
                            : 'active',
                    ]
                );
            }
        }
    }
}
