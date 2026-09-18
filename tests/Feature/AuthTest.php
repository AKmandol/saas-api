<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_user_can_register_company(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Test Company',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Registration successful.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'company',
                    'user',
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'email' => 'john@example.com',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);

        $this->assertTrue(
            $user->hasRole('owner')
        );

        $this->assertDatabaseHas('subscriptions', [
            'company_id' => $user->company_id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::where('email', 'owner@acme.test')->firstOrFail();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Login successful.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'roles',
                    'permissions',
                    'token',
                ],
            ]);

        $this->assertNotEmpty(
            $response->json('data.token')
        );
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@acme.test',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        $user = User::where('email', 'owner@acme.test')->firstOrFail();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'roles',
                    'permissions',
                ],
            ]);

        $this->assertEquals(
            $user->id,
            $response->json('data.user.id')
        );
    }

    public function test_unauthenticated_user_cannot_get_me(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('email', 'owner@acme.test')->firstOrFail();

        $token = $user->createToken('test-token');

        $plainTextToken = $token->plainTextToken;

        $response = $this
            ->withToken($plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Logged out successfully.',
            ]);

        $this->assertNull(
            PersonalAccessToken::findToken($plainTextToken)
        );
    }

    public function test_inactive_company_cannot_login(): void
    {
        $user = User::where('email', 'owner@acme.test')->firstOrFail();

        $user->company->update([
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'Your company account is inactive.',
            ]);
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'company_name',
                'name',
                'email',
                'password',
            ]);
    }
}
