<?php

declare(strict_types=1);

namespace App\Service\Messenger\InsertProduct;

use App\Entity\Product;
use App\Entity\Supplier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InsertMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(InsertProductMessage $message): void
    {
        $productDto = $message->getProduct();
        $supplierDto = $productDto->getSupplierDTO();

        $this->logger->debug('Attempting to find supplier', [
            'supplier_id' => $supplierDto->getId(),
            'supplier_name' => $supplierDto->getName(),
        ]);

        $supplier = $this->entityManager->getRepository(Supplier::class)
            ->findOneBy(['id' => $supplierDto->getId()]);

        if (!$supplier instanceof Supplier) {
            $this->logger->error('Supplier not found', [
                'supplier_name' => $supplierDto->getName(),
                'product_code' => $productDto->getCode(),
            ]);

            return;
        }

        $this->logger->debug('Supplier found', [
            'supplier_name' => $supplier->getName(),
        ]);

        $product = $this->entityManager->getRepository(Product::class)
            ->findOneBy(['code' => $productDto->getCode()]);

        if (!$product instanceof Product) {
            $product = new Product();
            $this->logger->debug('Creating new product', [
                'product_code' => $productDto->getCode(),
            ]);
        } else {
            $this->logger->debug('Updating existing product', [
                'product_code' => $productDto->getCode(),
            ]);
        }

        $product
            ->setDescription($productDto->getDescription())
            ->setCode($productDto->getCode())
            ->setPrice($productDto->getPrice())
            ->setSupplier($supplier);

        try {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->logger->info('Product successfully saved', [
                'product_code' => $productDto->getCode(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save product', [
                'product_code' => $productDto->getCode(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
