<?php

declare(strict_types=1);

namespace App\Service\Messenger\InsertProduct;

use App\Entity\Supplier;
use App\Enum\ProductState;
use App\Exception\ProductProcessingException;
use App\Factory\ProductFactory;
use App\Service\Provider\ProductProvider;
use App\Service\Provider\SupplierProvider;
use App\Service\Workflow\ProductCoordinator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InsertProductMessageHandler
{
    public function __construct(
        private readonly ProductFactory $productFactory,
        private readonly ProductCoordinator $workflowCoordinator,
        private readonly LoggerInterface $logger,
        private readonly SupplierProvider $supplierProvider,
        private readonly ProductProvider $productProvider
    ) {
    }

    public function __invoke(InsertProductMessage $message): void
    {
        $productDto = $message->getProduct();
        $supplierDto = $productDto->getSupplierDTO();

        try {
            $supplier = $this->supplierProvider->findSupplier($supplierDto);

            if (!$supplier instanceof Supplier) {
                $this->logger->error('Supplier not found', [
                    'supplier_name' => $supplierDto->getName(),
                    'product_code' => $productDto->getCode(),
                ]);

                return;
            }

            $product = $this->productProvider->findByCode($productDto->getCode());

            if ($product) {
                $this->productFactory->update($product, $productDto->getDescription(), $productDto->getPrice());

                if (ProductState::VALID === $product->getState() || ProductState::INVALID === $product->getState()) {
                    $this->workflowCoordinator->transitionToValidating($product);
                }

                $this->logger->debug('Updating existing product', [
                    'product_code' => $productDto->getCode(),
                ]);

                $this->productFactory->save($product);
            } else {
                $product = $this->productFactory->create(
                    $productDto->getCode(),
                    $productDto->getDescription(),
                    $productDto->getPrice(),
                    $supplier
                );

                $this->productFactory->save($product);

                $this->logger->debug('Creating new product', [
                    'product_code' => $productDto->getCode(),
                ]);
            }

            $this->logger->info('Product successfully saved', [
                'product_code' => $productDto->getCode(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error while processing product', [
                'product_code' => $productDto->getCode(),
                'error' => $e->getMessage(),
            ]);

            throw new ProductProcessingException('Unexpected error occurred while processing the product', $productDto->getCode(), 0, $e);
        }
    }
}
