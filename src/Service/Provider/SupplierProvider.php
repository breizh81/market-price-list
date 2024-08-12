<?php
declare(strict_types=1);

namespace App\Service\Provider;

use App\DTO\SupplierDTO;
use App\Entity\Supplier;
use Doctrine\ORM\EntityManagerInterface;

class SupplierProvider
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findSupplier(SupplierDTO $supplierDto): ?Supplier
    {
        return $this->entityManager->getRepository(Supplier::class)
            ->findOneBy(['id' => $supplierDto->getId()]);
    }
}
