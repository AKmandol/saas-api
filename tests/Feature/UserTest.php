<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
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

    private function admin(): User
    {
        return User::where('email', 'admin@acme.test')->firstOrFail();
    }

    private function manager(): User
    {
        return User::where('email', 'manager@acme.test')->firstOrFail();
    }

    private function globalOwner(): User
    {
        return User::where('email', 'owner@global.test')->firstOrFail();
    }

    public function test_authorized_user_can_create_user(): void
    {
        $owner = $this->owner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'company_id',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'company_id' => $owner->company_id,
            'email' => 'newuser@example.com',
        ]);

        $user = User::where('email', 'newuser@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('user'));
    }

    public function test_user_creation_requires_valid_data(): void
    {
        $response = $this
            ->actingAs($this->owner(), 'sanctum')
            ->postJson('/api/v1/users', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'role',
            ]);
    }

    public function test_user_email_must_be_unique_within_company(): void
    {
        $owner = $this->owner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Duplicate User',
                'email' => 'admin@acme.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_same_user_email_can_exist_in_different_companies(): void
    {
        $acme = $this->owner();
        $global = $this->globalOwner();

        $response = $this
            ->actingAs($global, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Shared Email User',
                'email' => 'admin@acme.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'company_id' => $acme->company_id,
            'email' => 'admin@acme.test',
        ]);

        $this->assertDatabaseHas('users', [
            'company_id' => $global->company_id,
            'email' => 'admin@acme.test',
        ]);
    }

    public function test_user_can_list_users_from_own_company(): void
    {
        $owner = $this->owner();
        $global = $this->globalOwner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->getJson('/api/v1/users');

        $response->assertOk();

        $response->assertJsonFragment([
            'email' => 'admin@acme.test',
        ]);

        $response->assertJsonMissing([
            'email' => 'admin@global.test',
        ]);
    }

    public function test_user_cannot_view_user_from_another_company(): void
    {
        $owner = $this->owner();
        $global = $this->globalOwner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->getJson("/api/v1/users/{$global->id}");

        $response->assertNotFound();
    }

    public function test_user_cannot_update_user_from_another_company(): void
    {
        $owner = $this->owner();
        $global = $this->globalOwner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->putJson("/api/v1/users/{$global->id}", [
                'name' => 'Hacked User',
            ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $global->id,
            'name' => $global->name,
        ]);
    }

    public function test_user_cannot_delete_user_from_another_company(): void
    {
        $owner = $this->owner();
        $global = $this->globalOwner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/users/{$global->id}");

        $response->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $global->id,
        ]);
    }

    public function test_authorized_user_can_update_user(): void
    {
        $owner = $this->owner();

        $user = User::create([
            'company_id' => $owner->company_id,
            'name' => 'Old Name',
            'email' => 'update-user@example.com',
            'password' => 'password123',
        ]);

        $user->assignRole('user');

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->putJson("/api/v1/users/{$user->id}", [
                'name' => 'Updated Name',
                'role' => 'manager',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'Updated Name'
            )
            ->assertJsonPath(
                'data.role',
                'manager'
            );

        $user->refresh();

        $this->assertTrue(
            $user->hasRole('manager')
        );
    }

    public function test_authorized_user_can_delete_user(): void
    {
        $owner = $this->owner();

        $user = User::create([
            'company_id' => $owner->company_id,
            'name' => 'Delete User',
            'email' => 'delete-user@example.com',
            'password' => 'password123',
        ]);

        $user->assignRole('user');

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $owner = $this->owner();

        $response = $this
            ->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/users/{$owner->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
        ]);
    }

    public function test_owner_cannot_be_deleted(): void
    {
        $owner = $this->owner();
        $admin = $this->admin();

        $response = $this
            ->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$owner->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
        ]);
    }

    public function test_user_without_create_permission_cannot_create_user(): void
    {
        $manager = $this->manager();

        $response = $this
            ->actingAs($manager, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Unauthorized User',
                'email' => 'unauthorized@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'unauthorized@example.com',
        ]);
    }

    public function test_user_without_update_permission_cannot_update_user(): void
    {
        $manager = $this->manager();
        $target = User::where('email', 'user@acme.test')->firstOrFail();

        $response = $this
            ->actingAs($manager, 'sanctum')
            ->putJson("/api/v1/users/{$target->id}", [
                'name' => 'Unauthorized Update',
            ]);

        $response->assertForbidden();
    }

    public function test_user_without_delete_permission_cannot_delete_user(): void
    {
        $manager = $this->manager();
        $target = User::where('email', 'user@acme.test')->firstOrFail();

        $response = $this
            ->actingAs($manager, 'sanctum')
            ->deleteJson("/api/v1/users/{$target->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
        ]);
    }
}
