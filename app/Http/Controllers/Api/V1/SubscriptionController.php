<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function show(Request $request)
    {
        $company = $request->user()->company;

        $subscription = $this->subscriptionService
            ->getActiveSubscription($company);

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription found.',
            ], 404);
        }

        return new SubscriptionResource($subscription);
    }

    public function plans()
    {
        $plans = Plan::query()
            ->with('features')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $plans->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'features' => $plan->features->map(function ($feature) {
                        return [
                            'feature' => $feature->feature,
                            'limit' => $feature->limit,
                        ];
                    })->values(),
                ];
            }),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id'),
            ],
        ]);

        $company = $request->user()->company;

        $plan = Plan::query()
            ->with('features')
            ->findOrFail($request->integer('plan_id'));

        $subscription = DB::transaction(function () use (
            $company,
            $plan
        ) {
            $subscription = Subscription::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if ($subscription) {
                $subscription->update([
                    'plan_id' => $plan->id,
                ]);

                return $subscription;
            }

            return Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);
        });

        return new SubscriptionResource(
            $subscription->fresh()->load('plan.features')
        );
    }
}
