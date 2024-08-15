<?php

declare(strict_types=1);

namespace App\Exception;

class ProductProcessingException extends \RuntimeException
{
    private string $productCode;

    public function __construct(string $message, string $productCode, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->productCode = $productCode;
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }
}
