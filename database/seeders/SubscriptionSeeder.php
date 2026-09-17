<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = [
            [
                'company_slug' => 'acme-technologies',
                'plan_slug' => 'pro',
            ],

            [
                'company_slug' => 'global-solutions',
                'plan_slug' => 'business',
            ],

            [
                'company_slug' => 'startup-labs',
                'plan_slug' => 'free',
            ],
        ];

        foreach ($subscriptions as $data) {

            $company = Company::where(
                'slug',
                $data['company_slug']
            )->firstOrFail();

            $plan = Plan::where(
                'slug',
                $data['plan_slug']
            )->firstOrFail();

            Subscription::updateOrCreate(
                [
                    'company_id' => $company->id,
                ],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => Carbon::now(),
                    'ends_at' => Carbon::now()->addMonth(),
                ]
            );
        }
    }
}
