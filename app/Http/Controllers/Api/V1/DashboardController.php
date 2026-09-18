<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly DashboardCacheService $dashboardCacheService
    ) {}

    public function index(Request $request)
    {
        $company = $request->user()->company;

        $cacheKey = $this->dashboardCacheService->key($company->id);

        $dashboard = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($company) {
                return $this->buildDashboard($company);
            }
        );

        return response()->json([
            'data' => $dashboard,
        ]);
    }

    private function buildDashboard(Company $company): array
    {
        $companyId = $company->id;

        $customerStats = Customer::query()
            ->where('company_id', $companyId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive"
            )
            ->first();

        $userStats = User::query()
            ->where('company_id', $companyId)
            ->selectRaw('COUNT(*) as total')
            ->first();

        $subscription = $this->subscriptionService
            ->getActiveSubscription($company);

        return [
            'customers' => [
                'total' => (int) $customerStats->total,
                'active' => (int) $customerStats->active,
                'inactive' => (int) $customerStats->inactive,
            ],

            'users' => [
                'total' => (int) $userStats->total,
            ],

            'subscription' => $subscription ? [
                'plan' => $subscription->plan->name,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at?->toISOString(),
                'ends_at' => $subscription->ends_at?->toISOString(),
            ] : null,
        ];
    }
}
