<?php

declare(strict_types=1);

namespace App\Service\Messenger\InsertProduct;

use App\DTO\ProductDTO;

class InsertProductMessage
{
    public function __construct(
        private readonly ProductDTO $product,
        private readonly int $importBatchId
    ) {
    }

    public function getProduct(): ProductDTO
    {
        return $this->product;
    }

    public function getImportBatchId(): int
    {
        return $this->importBatchId;
    }
}
