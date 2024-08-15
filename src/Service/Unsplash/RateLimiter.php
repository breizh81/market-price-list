<?php

declare(strict_types=1);

namespace App\Service\Unsplash;

use App\Exception\SearchPhotosException;

class RateLimiter
{
    private array $apiCallTimestamps = [];

    public function __construct(private readonly int $maxCallsPerHour)
    {
    }

    public function checkRateLimit(): void
    {
        $now = time();
        $this->apiCallTimestamps = array_filter(
            $this->apiCallTimestamps,
            fn ($timestamp) => ($now - $timestamp) <= 3600
        );

        if (\count($this->apiCallTimestamps) >= $this->maxCallsPerHour) {
            throw new SearchPhotosException(429, 'Rate limit exceeded: Only '.$this->maxCallsPerHour.' API calls are allowed per hour');
        }
    }

    public function logApiCall(): void
    {
        $this->apiCallTimestamps[] = time();
    }
}
