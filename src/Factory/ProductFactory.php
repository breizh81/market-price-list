<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Product;
use App\Entity\Supplier;
use App\Enum\ProductState;
use Doctrine\ORM\EntityManagerInterface;

class ProductFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function create(
        string $code,
        string $description,
        float $price,
        Supplier $supplier
    ): Product {
        $product = new Product();
        $product->setCode($code)
            ->setDescription($description)
            ->setPrice($price)
            ->setSupplier($supplier)
            ->setState(ProductState::NEW);

        return $product;
    }

    public function update(Product $product, string $description, float $price): void
    {
        $product
            ->setDescription($description)
            ->setPrice($price);
    }

    public function persist(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function save(): void
    {
        $this->entityManager->flush();
    }
}
