<?php

namespace App\Exceptions;

use Exception;

class FeatureLimitExceededException extends Exception
{
    public function __construct(
        public readonly string $feature,
        public readonly int $limit
    ) {
        parent::__construct(
            "Your {$feature} limit of {$limit} has been reached."
        );
    }
}
