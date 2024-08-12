<?php
declare(strict_types=1);

namespace App\Service\Provider;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductProvider
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findByCode(string $code): ?Product
    {
        return $this->entityManager->getRepository(Product::class)
            ->findOneBy(['code' => $code]);
    }
}
