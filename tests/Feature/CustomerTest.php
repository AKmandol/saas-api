<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    private function globalOwner(): User
    {
        return User::where('email', 'owner@global.test')->firstOrFail();
    }

    public function test_authorized_user_can_create_customer(): void
    {
        $user = $this->owner();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/customers', [
                'name' => 'New Customer',
                'email' => 'newcustomer@example.com',
                'phone' => '+8801800000099',
                'status' => 'active',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'company_id' => $user->company_id,
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
        ]);
    }

    public function test_customer_creation_requires_valid_data(): void
    {
        $response = $this
            ->actingAs($this->owner(), 'sanctum')
            ->postJson('/api/v1/customers', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
            ]);
    }

    public function test_customer_email_must_be_unique_within_company(): void
    {
        $user = $this->owner();

        Customer::create([
            'company_id' => $user->company_id,
            'name' => 'Existing Customer',
            'email' => 'duplicate@example.com',
            'phone' => '+8801800000011',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/customers', [
                'name' => 'Another Customer',
                'email' => 'duplicate@example.com',
                'phone' => '+8801800000022',
                'status' => 'active',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_same_customer_email_can_exist_in_different_companies(): void
    {
        $acme = $this->owner();
        $global = $this->globalOwner();

        Customer::create([
            'company_id' => $acme->company_id,
            'name' => 'Acme Customer',
            'email' => 'shared@example.com',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($global, 'sanctum')
            ->postJson('/api/v1/customers', [
                'name' => 'Global Customer',
                'email' => 'shared@example.com',
                'status' => 'active',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('customers', [
            'company_id' => $acme->company_id,
            'email' => 'shared@example.com',
        ]);

        $this->assertDatabaseHas('customers', [
            'company_id' => $global->company_id,
            'email' => 'shared@example.com',
        ]);
    }

    public function test_user_can_list_only_customers_from_their_company(): void
    {
        $acme = $this->owner();
        $global = $this->globalOwner();

        Customer::create([
            'company_id' => $acme->company_id,
            'name' => 'Acme Private Customer',
            'email' => 'acme-private@example.com',
            'status' => 'active',
        ]);

        Customer::create([
            'company_id' => $global->company_id,
            'name' => 'Global Private Customer',
            'email' => 'global-private@example.com',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($acme, 'sanctum')
            ->getJson('/api/v1/customers');

        $response->assertOk();

        $response->assertJsonFragment([
            'email' => 'acme-private@example.com',
        ]);

        $response->assertJsonMissing([
            'email' => 'global-private@example.com',
        ]);
    }

    public function test_user_cannot_view_customer_from_another_company(): void
    {
        $acme = $this->owner();
        $global = $this->globalOwner();

        $customer = Customer::create([
            'company_id' => $global->company_id,
            'name' => 'Global Customer',
            'email' => 'global@example.com',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($acme, 'sanctum')
            ->getJson("/api/v1/customers/{$customer->id}");

        $response->assertNotFound();
    }

    public function test_user_cannot_update_customer_from_another_company(): void
    {
        $acme = $this->owner();
        $global = $this->globalOwner();

        $customer = Customer::create([
            'company_id' => $global->company_id,
            'name' => 'Global Customer',
            'email' => 'global@example.com',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($acme, 'sanctum')
            ->putJson("/api/v1/customers/{$customer->id}", [
                'name' => 'Hacked Customer',
            ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Global Customer',
        ]);
    }

    public function test_user_cannot_delete_customer_from_another_company(): void
    {
        $acme = $this->owner();
        $global = $this->globalOwner();

        $customer = Customer::create([
            'company_id' => $global->company_id,
            'name' => 'Global Customer',
            'email' => 'global@example.com',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($acme, 'sanctum')
            ->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertNotFound();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_user_can_update_own_company_customer(): void
    {
        $user = $this->owner();

        $customer = Customer::create([
            'company_id' => $user->company_id,
            'name' => 'Old Name',
            'email' => 'customer-update@example.com',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson("/api/v1/customers/{$customer->id}", [
                'name' => 'Updated Name',
                'status' => 'inactive',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'Updated Name'
            );

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'status' => 'inactive',
        ]);
    }

    public function test_user_can_delete_own_company_customer(): void
    {
        $user = $this->owner();

        $customer = Customer::create([
            'company_id' => $user->company_id,
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
