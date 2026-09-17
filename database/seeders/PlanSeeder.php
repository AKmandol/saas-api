<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'price' => 0,
                'billing_interval' => 'monthly',
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'price' => 49.00,
                'billing_interval' => 'monthly',
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business',
                'price' => 149.00,
                'billing_interval' => 'monthly',
                'is_active' => true,
            ]
        );
    }
}
