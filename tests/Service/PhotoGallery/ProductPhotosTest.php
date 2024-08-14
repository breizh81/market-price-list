<?php

declare(strict_types=1);

namespace App\Tests\Service\PhotoGallery;

use App\Service\PhotoGallery\ProductPhotos;
use App\Service\Unsplash\SearchPhotos;
use PHPUnit\Framework\TestCase;

class ProductPhotosTest extends TestCase
{
    private SearchPhotos $searchPhotos;
    private ProductPhotos $productPhotos;

    protected function setUp(): void
    {
        $this->searchPhotos = $this->createMock(SearchPhotos::class);
        $this->productPhotos = new ProductPhotos($this->searchPhotos);
    }

    public function testGetProductPhotos(): void
    {
        $product1 = $this->createMock(\App\Entity\Product::class);
        $product1->method('getDescription')->willReturn('Product 1 Description');
        $product1->method('getId')->willReturn(1);

        $product2 = $this->createMock(\App\Entity\Product::class);
        $product2->method('getDescription')->willReturn('Product 2 Description');
        $product2->method('getId')->willReturn(2);

        $this->searchPhotos->method('searchPhotos')
            ->willReturnMap([
                ['Product 1 Description', ['results' => ['photo1', 'photo2']]],
                ['Product 2 Description', ['results' => ['photo3', 'photo4']]],
            ]);

        $products = [$product1, $product2];
        $result = $this->productPhotos->getProductPhotos($products);

        $expected = [
            1 => ['photo1', 'photo2'],
            2 => ['photo3', 'photo4'],
        ];

        $this->assertEquals($expected, $result);
    }
}
