<?php

declare(strict_types=1);

namespace App\Tests\Service\Messenger\InsertProduct;

use App\DTO\ProductDTO;
use App\DTO\SupplierDTO;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Enum\ProductState;
use App\Exception\ProductProcessingException;
use App\Factory\ProductFactory;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use App\Service\Messenger\InsertProduct\InsertProductMessageHandler;
use App\Service\Provider\ProductProvider;
use App\Service\Provider\SupplierProvider;
use App\Service\Workflow\ProductCoordinator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InsertProductMessageHandlerTest extends TestCase
{
    private ProductFactory $productFactory;
    private ProductCoordinator $workflowCoordinator;
    private LoggerInterface $logger;
    private SupplierProvider $supplierProvider;
    private ProductProvider $productProvider;
    private InsertProductMessageHandler $handler;
    private InsertProductMessage $message;
    private ProductDTO $productDto;
    private SupplierDTO $supplierDto;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        $this->productFactory = $this->createMock(ProductFactory::class);
        $this->workflowCoordinator = $this->createMock(ProductCoordinator::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->supplierProvider = $this->createMock(SupplierProvider::class);
        $this->productProvider = $this->createMock(ProductProvider::class);
        $this->handler = new InsertProductMessageHandler(
            $this->productFactory,
            $this->workflowCoordinator,
            $this->logger,
            $this->supplierProvider,
            $this->productProvider
        );

        $this->productDto = $this->createMock(ProductDTO::class);
        $this->supplierDto = $this->createMock(SupplierDTO::class);
        $this->supplier = $this->createMock(Supplier::class);
        $this->product = $this->createMock(Product::class);
        $this->message = new InsertProductMessage($this->productDto, 123);
    }

    public function testInvokeWithExistingProduct(): void
    {
        $this->productDto->method('getSupplierDTO')->willReturn($this->supplierDto);
        $this->supplierProvider->method('findSupplier')->with($this->supplierDto)->willReturn($this->supplier);
        $this->productProvider->method('findByCode')->with($this->productDto->getCode())->willReturn($this->product);

        $this->product->method('getState')->willReturn(ProductState::NEW);

        $this->productFactory->expects($this->once())->method('update')->with(
            $this->product,
            $this->productDto->getDescription(),
            $this->productDto->getPrice()
        );
        $this->productFactory->expects($this->once())->method('save')->with($this->product);

        $this->handler->__invoke($this->message);
    }

    public function testInvokeWithNewProduct(): void
    {
        $this->productDto->method('getSupplierDTO')->willReturn($this->supplierDto);
        $this->supplierProvider->method('findSupplier')->with($this->supplierDto)->willReturn($this->supplier);
        $this->productProvider->method('findByCode')->with($this->productDto->getCode())->willReturn(null);

        $this->productFactory->expects($this->once())->method('create')->with(
            $this->productDto->getCode(),
            $this->productDto->getDescription(),
            $this->productDto->getPrice(),
            $this->supplier
        )->willReturn($this->product);
        $this->productFactory->expects($this->once())->method('save')->with($this->product);

        $this->handler->__invoke($this->message);
    }

    public function testInvokeWithSupplierNotFound(): void
    {
        $this->productDto->method('getSupplierDTO')->willReturn($this->supplierDto);
        $this->supplierProvider->method('findSupplier')->with($this->supplierDto)->willReturn(null);

        $this->logger->expects($this->once())->method('error')->with('Supplier not found', [
            'supplier_name' => $this->supplierDto->getName(),
            'product_code' => $this->productDto->getCode(),
        ]);

        $this->handler->__invoke($this->message);
    }

    public function testInvokeWithException(): void
    {
        $this->productDto->method('getSupplierDTO')->willReturn($this->supplierDto);
        $this->supplierProvider->method('findSupplier')->with($this->supplierDto)->willReturn($this->supplier);
        $this->productProvider->method('findByCode')->with($this->productDto->getCode())
            ->willThrowException(new \Exception('Unexpected error'));

        $this->logger->expects($this->once())->method('error')->with('Unexpected error while processing product', [
            'product_code' => $this->productDto->getCode(),
            'error' => 'Unexpected error',
        ]);

        $this->expectException(ProductProcessingException::class);
        $this->expectExceptionMessage('Unexpected error occurred while processing the product');

        $this->handler->__invoke($this->message);
    }
}
