<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function owner(): User
    {
        return User::where('email', 'owner@acme.test')->firstOrFail();
    }

    public function test_user_can_view_current_subscription(): void
    {
        $response = $this
            ->actingAs($this->owner(), 'sanctum')
            ->getJson('/api/v1/subscription');

        $response->dump();

        $response->assertOk();
    }

    public function test_user_can_view_available_plans(): void
    {
        $response = $this
            ->actingAs($this->owner(), 'sanctum')
            ->getJson('/api/v1/plans');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'features',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(
            3,
            count($response->json('data'))
        );
    }

    public function test_subscription_service_returns_current_plan(): void
    {
        $owner = $this->owner();

        $service = app(SubscriptionService::class);

        $plan = $service->getCurrentPlan($owner->company);

        $this->assertNotNull($plan);

        $this->assertEquals(
            'pro',
            $plan->slug
        );
    }

    public function test_subscription_service_returns_feature_limit(): void
    {
        $owner = $this->owner();

        $service = app(SubscriptionService::class);

        $limit = $service->getFeatureLimit(
            $owner->company,
            'customers'
        );

        $this->assertEquals(
            1000,
            $limit
        );
    }

    public function test_company_can_upgrade_subscription(): void
    {
        $owner = $this->owner();

        $businessPlan = Plan::where('slug', 'business')->firstOrFail();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->putJson('/api/v1/subscription', [
                'plan_id' => $businessPlan->id,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.plan.slug',
                'business'
            );

        $this->assertDatabaseHas('subscriptions', [
            'company_id' => $owner->company_id,
            'plan_id' => $businessPlan->id,
            'status' => 'active',
        ]);
    }

    public function test_customer_creation_is_blocked_when_plan_limit_is_reached(): void
    {
        $owner = $this->owner();

        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        $subscription = $owner->company->subscription;

        $subscription->update([
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'ends_at' => now()->addMonth(),
        ]);

        $limit = $freePlan->features()
            ->where('feature', 'customers')
            ->value('limit');

        Customer::where('company_id', $owner->company_id)
            ->delete();

        for ($i = 1; $i <= $limit; $i++) {
            Customer::create([
                'company_id' => $owner->company_id,
                'name' => "Customer {$i}",
                'email' => "customer-limit-{$i}@example.com",
                'status' => 'active',
            ]);
        }

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/customers', [
                'name' => 'Over Limit Customer',
                'email' => 'over-limit@example.com',
                'status' => 'active',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'feature' => 'customers',
                'limit' => $limit,
            ]);
    }

    public function test_user_creation_is_blocked_when_plan_limit_is_reached(): void
    {
        $owner = $this->owner();

        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        $subscription = $owner->company->subscription;

        $subscription->update([
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'ends_at' => now()->addMonth(),
        ]);

        $limit = $freePlan->features()
            ->where('feature', 'users')
            ->value('limit');

        User::where('company_id', $owner->company_id)
            ->where('id', '!=', $owner->id)
            ->delete();

        for ($i = 1; $i < $limit; $i++) {
            $user = User::create([
                'company_id' => $owner->company_id,
                'name' => "Limit User {$i}",
                'email' => "limit-user-{$i}@example.com",
                'password' => 'password123',
            ]);

            $user->assignRole('user');
        }

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Over Limit User',
                'email' => 'over-limit-user@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'feature' => 'users',
                'limit' => $limit,
            ]);
    }

    public function test_inactive_subscription_does_not_provide_feature_access(): void
    {
        $owner = $this->owner();

        $subscription = $owner->company->subscription;

        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => now()->subDay(),
        ]);

        $service = app(SubscriptionService::class);

        $this->assertNull(
            $service->getCurrentPlan($owner->company)
        );

        $this->assertFalse(
            $service->canUseFeature(
                $owner->company,
                'customers',
                0
            )
        );
    }
}
