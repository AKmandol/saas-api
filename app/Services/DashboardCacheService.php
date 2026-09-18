<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    public function key(int $companyId): string
    {
        return "company:{$companyId}:dashboard";
    }

    public function forget(int $companyId): void
    {
        Cache::forget(
            $this->key($companyId)
        );
    }
}
