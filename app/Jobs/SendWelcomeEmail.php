<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public int $userId
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        Log::info('Welcome email sent', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Actual Mail/Mailable can be added here.
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Welcome email job failed', [
            'user_id' => $this->userId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
