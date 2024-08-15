<?php

declare(strict_types=1);

namespace App\Service\PhotoGallery;

use App\Service\Unsplash\SearchPhotos;

class ProductPhotos
{
    public function __construct(private readonly SearchPhotos $searchPhotos)
    {
    }

    public function getProductPhotos(array $products): array
    {
        $productPhotos = [];

        foreach ($products as $product) {
            $description = $product->getDescription();
            $photos = $this->searchPhotos->searchPhotos($description);
            $productPhotos[$product->getId()] = $photos['results'];
        }

        return $productPhotos;
    }
}
