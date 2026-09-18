<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_user_can_view_dashboard(): void
    {
        $response = $this
            ->actingAs($this->owner(), 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'customers' => [
                        'total',
                        'active',
                        'inactive',
                    ],
                    'users' => [
                        'total',
                    ],
                    'subscription' => [
                        'plan',
                        'status',
                        'starts_at',
                        'ends_at',
                    ],
                ],
            ]);
    }

    public function test_dashboard_only_contains_current_company_data(): void
    {
        $owner = $this->owner();

        // Add one extra customer to company 1 so its count is
        // intentionally different from company 2.
        Customer::create([
            'company_id' => 1,
            'name' => 'Dashboard Test Customer',
            'email' => 'dashboard-test@acme.test',
            'phone' => '+8801800000099',
            'status' => 'active',
        ]);

        $companyOneCustomerCount = Customer::where('company_id', 1)->count();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response->assertOk();

        $dashboardCustomerCount = $response->json(
            'data.customers.total'
        );

        $companyTwoCustomerCount = Customer::where('company_id', 2)->count();

        $this->assertSame(
            $companyOneCustomerCount,
            $dashboardCustomerCount
        );

        $this->assertNotSame(
            $companyTwoCustomerCount,
            $dashboardCustomerCount
        );
    }

    public function test_dashboard_is_cached(): void
    {
        Cache::flush();

        $owner = $this->owner();

        $cacheKey = "company:{$owner->company_id}:dashboard";

        $this->assertFalse(Cache::has($cacheKey));

        $this
            ->actingAs($owner, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk();

        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_dashboard_cache_is_company_specific(): void
    {
        Cache::flush();

        $ownerOne = User::where('email', 'owner@acme.test')->firstOrFail();
        $ownerTwo = User::where('email', 'owner@global.test')->firstOrFail();

        $this
            ->actingAs($ownerOne, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk();

        $this
            ->actingAs($ownerTwo, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk();

        $this->assertTrue(
            Cache::has("company:{$ownerOne->company_id}:dashboard")
        );

        $this->assertTrue(
            Cache::has("company:{$ownerTwo->company_id}:dashboard")
        );
    }

    public function test_user_without_dashboard_permission_gets_forbidden(): void
    {
        $user = User::where('email', 'user@acme.test')->firstOrFail();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response->assertForbidden();
    }
}
