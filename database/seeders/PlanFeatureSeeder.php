<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'free' => [
                'users' => 5,
                'customers' => 100,
            ],

            'pro' => [
                'users' => 20,
                'customers' => 1000,
            ],

            'business' => [
                'users' => 100,
                'customers' => 10000,
            ],
        ];

        foreach ($features as $planSlug => $planFeatures) {

            $plan = Plan::where('slug', $planSlug)->firstOrFail();

            foreach ($planFeatures as $feature => $limit) {

                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature' => $feature,
                    ],
                    [
                        'limit' => $limit,
                    ]
                );
            }
        }
    }
}
