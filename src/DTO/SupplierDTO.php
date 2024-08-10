<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Supplier;

class SupplierDTO
{
    public function __construct(private readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromEntity(Supplier $supplier): self
    {
        return new self($supplier->getName());
    }
}
