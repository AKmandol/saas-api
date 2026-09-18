<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        RateLimiter::clear('user:1');
        RateLimiter::clear('user:2');
    }

    private function owner(): User
    {
        return User::where('email', 'owner@acme.test')->firstOrFail();
    }

    public function test_authenticated_api_is_rate_limited(): void
    {
        $owner = $this->owner();

        for ($i = 0; $i < 60; $i++) {
            $this
                ->actingAs($owner, 'sanctum')
                ->getJson('/api/v1/company')
                ->assertOk();
        }

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->getJson('/api/v1/company');

        $response->assertStatus(429);
    }

    public function test_authenticated_rate_limit_is_per_user(): void
    {
        $ownerOne = User::where('email', 'owner@acme.test')->firstOrFail();
        $ownerTwo = User::where('email', 'owner@global.test')->firstOrFail();

        // Consume company 1 user's entire limit.
        for ($i = 0; $i < 60; $i++) {
            $this
                ->actingAs($ownerOne, 'sanctum')
                ->getJson('/api/v1/company')
                ->assertOk();
        }

        $this
            ->actingAs($ownerOne, 'sanctum')
            ->getJson('/api/v1/company')
            ->assertStatus(429);

        // Company 2/user 2 has its own rate-limit bucket.
        $this
            ->actingAs($ownerTwo, 'sanctum')
            ->getJson('/api/v1/company')
            ->assertOk();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $email = 'owner@acme.test';

        for ($i = 0; $i < 5; $i++) {
            $this
                ->postJson('/api/v1/auth/login', [
                    'email' => $email,
                    'password' => 'wrong-password',
                ])
                ->assertStatus(422);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_login_rate_limit_is_based_on_email_and_ip(): void
    {
        $emailOne = 'owner@acme.test';
        $emailTwo = 'owner@global.test';

        for ($i = 0; $i < 5; $i++) {
            $this
                ->postJson('/api/v1/auth/login', [
                    'email' => $emailOne,
                    'password' => 'wrong-password',
                ])
                ->assertStatus(422);
        }

        $this
            ->postJson('/api/v1/auth/login', [
                'email' => $emailOne,
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);

        // Different email gets a different rate-limit key.
        $this
            ->postJson('/api/v1/auth/login', [
                'email' => $emailTwo,
                'password' => 'wrong-password',
            ])
            ->assertStatus(422);
    }
}
