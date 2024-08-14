<?php

declare(strict_types=1);

namespace App\Tests\Service\Unsplash;

use App\Exception\SearchPhotosException;
use App\Service\Unsplash\RateLimiter;
use App\Service\Unsplash\SearchPhotos;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SearchPhotosTest extends TestCase
{
    private MockObject $httpClientMock;
    private MockObject $rateLimiterMock;
    private SearchPhotos $searchPhotos;
    private string $apiKey = 'unsplah_api_key';

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->rateLimiterMock = $this->createMock(RateLimiter::class);
        $this->searchPhotos = new SearchPhotos($this->httpClientMock, $this->apiKey, $this->rateLimiterMock);
    }

    public function testSearchPhotos(): void
    {
        $expectedResponse = [
            'results' => [
                [
                    'id' => '1981345689',
                    'urls' => ['small' => 'https://unsplash.com/kiwi.jpg'],
                    'alt_description' => 'photo of kiwi fruit',
                ],
            ],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn($expectedResponse);

        $this->rateLimiterMock->expects($this->once())->method('checkRateLimit');
        $this->rateLimiterMock->expects($this->once())->method('logApiCall');

        $this->httpClientMock
            ->method('request')
            ->with(
                'GET',
                'https://api.unsplash.com/search/photos',
                $this->callback(function ($options) {
                    return 'kiwi fruit' === $options['query']['query']
                        && 1 === $options['query']['per_page']
                        && $options['query']['client_id'] === $this->apiKey;
                })
            )
            ->willReturn($responseMock);

        $result = $this->searchPhotos->searchPhotos('kiwi fruit');

        $this->assertEquals($expectedResponse, $result);
    }

    public function testSearchPhotosThrowsExceptionOnTransportError(): void
    {
        $this->rateLimiterMock->expects($this->once())->method('checkRateLimit');

        $this->httpClientMock
            ->method('request')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $this->expectException(SearchPhotosException::class);
        $this->expectExceptionMessage('Failed to fetch photos from Unsplash API');

        $this->searchPhotos->searchPhotos('kiwi fruit');
    }

    public function testSearchPhotosThrowsExceptionWhenRateLimitExceeded(): void
    {
        $this->rateLimiterMock->expects($this->once())
            ->method('checkRateLimit')
            ->willThrowException(new SearchPhotosException(
                429,
                'Rate limit exceeded: Only 50 API calls are allowed per hour'
            ));

        $this->expectException(SearchPhotosException::class);
        $this->expectExceptionMessage('Rate limit exceeded: Only 50 API calls are allowed per hour');

        $this->searchPhotos->searchPhotos('kiwi fruit');
    }
}
