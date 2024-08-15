<?php

declare(strict_types=1);

namespace App\DTO;

class ProductDTO
{
    public function __construct(
        private readonly string $description,
        private readonly string $code,
        private readonly float $price,
        private readonly SupplierDTO $supplierDTO,
        private readonly ImportBatchDTO $importBatchDTO
    ) {
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSupplierDTO(): SupplierDTO
    {
        return $this->supplierDTO;
    }

    public function getImportBatchDTO(): ImportBatchDTO
    {
        return $this->importBatchDTO;
    }

    public static function fromArray(array $data): self
    {
        return new self($data[0], $data[1], (float) $data[2], $data['supplierDTO'], $data['importBatchDTO']);
    }
}
