<?php

declare(strict_types=1);

namespace App\Service\Unsplash;

use App\Exception\SearchPhotosException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SearchPhotos
{
    public function __construct(
        readonly private HttpClientInterface $httpClient,
        readonly private string $apiKey,
        readonly private RateLimiter $rateLimiter
    ) {
    }

    public function searchPhotos(string $query, int $perPage = 1): array
    {
        $this->rateLimiter->checkRateLimit();

        try {
            $response = $this->httpClient->request(
                'GET',
                'https://api.unsplash.com/search/photos',
                [
                    'query' => [
                        'query' => $query,
                        'per_page' => $perPage,
                        'client_id' => $this->apiKey,
                    ],
                ],
            );

            $this->rateLimiter->logApiCall();

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new SearchPhotosException($e->getCode(), 'Failed to fetch photos from Unsplash API', $e);
        }
    }
}
