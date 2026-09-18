<?php

namespace Tests\Feature;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_registration_dispatches_welcome_email_job(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Queue Test Company',
            'name' => 'Queue Test User',
            'email' => 'queue-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();

        $user = User::where(
            'email',
            'queue-test@example.com'
        )->firstOrFail();

        Queue::assertPushed(
            SendWelcomeEmail::class,
            function (SendWelcomeEmail $job) use ($user) {
                return $job->userId === $user->id;
            }
        );
    }

    public function test_welcome_email_job_can_be_executed(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'name' => 'Queue User',
            'email' => 'queue-user@example.com',
        ]);

        $job = new SendWelcomeEmail($user->id);

        $job->handle();

        $this->assertTrue(true);
    }

    public function test_welcome_email_job_has_retry_configuration(): void
    {
        $job = new SendWelcomeEmail(1);

        $this->assertSame(3, $job->tries);
        $this->assertSame(30, $job->timeout);
        $this->assertSame(
            [10, 30, 60],
            $job->backoff()
        );
    }

    public function test_welcome_email_job_does_not_fail_for_missing_user(): void
    {
        $job = new SendWelcomeEmail(999999);

        $job->handle();

        $this->assertTrue(true);
    }
}
