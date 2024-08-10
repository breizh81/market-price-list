<?php

declare(strict_types=1);

namespace App\Service\Messenger\InsertProduct;

use App\Entity\Product;
use App\Repository\ProductRepository;

class InsertMessageHandler
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    public function __invoke(InsertProductMessage $message)
    {
        $productDto = $message->getProduct();
        $product = $this->productRepository->findOneBy(['code' => $productDto->getCode()]);

        if (!$product) {
            $product = new Product();
        }
        $product
            ->setDescription($productDto->getDescription())
            ->setCode($productDto->getCode())
            ->setPrice($productDto->getPrice());

        $this->productRepository->save($product);
    }
}
