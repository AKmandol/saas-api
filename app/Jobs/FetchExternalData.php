<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class FetchExternalData implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public string $endpoint
    ) {
    }

    public function handle(): void
    {
        $executed = RateLimiter::attempt(
            'external-api',
            10,
            function () {
                $response = Http::timeout(10)
                    ->get($this->endpoint);

                $response->throw();

                // Process/store response here.
            },
            1
        );

        if (!$executed) {
            $this->release(1);
        }
    }

    public function backoff(): array
    {
        return [5, 15, 30, 60];
    }

    public function failed(?Throwable $exception): void
    {
        report($exception);
    }
}
