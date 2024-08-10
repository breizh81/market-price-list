<?php

declare(strict_types=1);

namespace App\Service\Messenger\InsertProduct;

use App\DTO\ProductDTO;

class InsertProductMessage
{
    public function __construct(private readonly ProductDTO $product)
    {
    }

    public function getProduct(): ProductDTO
    {
        return $this->product;
    }
}
