<?php

declare(strict_types=1);

namespace App\Tests\Service\Unsplash;

use App\Exception\SearchPhotosException;
use App\Service\Unsplash\RateLimiter;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    public function testCheckRateLimitAllowsCallsWithinLimit(): void
    {
        $rateLimiter = new RateLimiter(50);

        for ($i = 0; $i < 49; ++$i) {
            $rateLimiter->logApiCall();
        }

        $this->expectNotToPerformAssertions();
        $rateLimiter->checkRateLimit();
    }

    public function testCheckRateLimitBlocksCallsExceedingLimit(): void
    {
        $rateLimiter = new RateLimiter(50);

        for ($i = 0; $i < 50; ++$i) {
            $rateLimiter->logApiCall();
        }

        $this->expectException(SearchPhotosException::class);
        $this->expectExceptionMessage('Rate limit exceeded: Only 50 API calls are allowed per hour');

        $rateLimiter->checkRateLimit();
    }

    public function testCheckRateLimitAllowsNewCallsAfterAnHour(): void
    {
        $rateLimiter = new RateLimiter(2);

        // Simulate 2 API calls, with the first one being an hour ago
        $this->simulateApiCall($rateLimiter, time() - 3601); // 1 hour and 1 second ago
        $rateLimiter->logApiCall(); // Recent call

        // Should not throw an exception because the first call is older than an hour
        $this->expectNotToPerformAssertions();
        $rateLimiter->checkRateLimit();
    }

    private function simulateApiCall(RateLimiter $rateLimiter, int $timestamp): void
    {
        $reflection = new \ReflectionClass($rateLimiter);
        $property = $reflection->getProperty('apiCallTimestamps');
        $timestamps = $property->getValue($rateLimiter);
        $timestamps[] = $timestamp;
        $property->setValue($rateLimiter, $timestamps);
    }
}
