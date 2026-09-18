<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Exceptions\FeatureLimitExceededException;
use RuntimeException;

class SubscriptionService
{
    public function getActiveSubscription(Company $company): ?Subscription
    {
        return $company->subscription()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->with('plan.features')
            ->first();
    }

    public function getCurrentPlan(Company $company): ?Plan
    {
        return $this->getActiveSubscription($company)?->plan;
    }

    public function getFeatureLimit(
        Company $company,
        string $feature
    ): ?int {
        $plan = $this->getCurrentPlan($company);

        if (!$plan) {
            return null;
        }

        return $plan->features
            ->firstWhere('feature', $feature)
            ?->limit;
    }

    public function canUseFeature(
        Company $company,
        string $feature,
        int $currentUsage
    ): bool {
        $limit = $this->getFeatureLimit($company, $feature);

        if ($limit === null) {
            return false;
        }

        return $currentUsage < $limit;
    }

    public function ensureWithinLimit(
        Company $company,
        string $feature,
        int $currentUsage
    ): void {
        $limit = $this->getFeatureLimit($company, $feature);

        if ($limit === null) {
            throw new RuntimeException(
                "Feature [{$feature}] is not available for this subscription."
            );
        }

        if ($currentUsage >= $limit) {
            throw new FeatureLimitExceededException(
                $feature,
                $limit
            );
        }
    }
}
