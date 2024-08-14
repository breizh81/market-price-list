<?php

declare(strict_types=1);

namespace App\Tests\Service\Unsplash;

use App\Exception\SearchPhotosException;
use App\Service\Unsplash\RateLimiter;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    public function testCheckRateLimitDoesNotThrowExceptionWhenUnderLimit(): void
    {
        $rateLimiter = new RateLimiter(50);

        for ($i = 0; $i < 49; ++$i) {
            $rateLimiter->logApiCall();
        }

        $this->expectNotToPerformAssertions();

        $rateLimiter->checkRateLimit();
    }

    public function testCheckRateLimitThrowsExceptionWhenOverLimit(): void
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
        $rateLimiter = new RateLimiter(50);

        for ($i = 0; $i < 50; ++$i) {
            $rateLimiter->logApiCall();
        }

        sleep(1);

        $rateLimiter->checkRateLimit();

        $this->expectNotToPerformAssertions();

        $rateLimiter->logApiCall();
    }
}
