<?php

declare(strict_types=1);

namespace App\Tests\Service\Validator;

use App\Entity\Supplier;
use App\Exception\ProductImportException;
use App\Service\Validator\ProductImportValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductImportValidatorTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ProductImportValidator $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = new ProductImportValidator($this->entityManager);
    }

    public function testValidateFileWithValidFile(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $this->assertSame($file, $this->validator->validateFile($file));
    }

    public function testValidateFileWithInvalidFile(): void
    {
        $this->expectException(ProductImportException::class);
        $this->expectExceptionMessage('Invalid file type');
        $this->validator->validateFile('invalid_file');
    }

    public function testValidateSupplierWithValidSupplier(): void
    {
        $supplier = $this->createMock(Supplier::class);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('find')->with(1)->willReturn($supplier);

        $this->entityManager->method('getRepository')
            ->with(Supplier::class)
            ->willReturn($repository);

        $this->assertSame($supplier, $this->validator->validateSupplier(1));
    }

    public function testValidateSupplierWithInvalidSupplier(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('find')->with(1)->willReturn(null);

        $this->entityManager->method('getRepository')
            ->with(Supplier::class)
            ->willReturn($repository);

        $this->expectException(ProductImportException::class);
        $this->expectExceptionMessage('Supplier not found');
        $this->validator->validateSupplier(1);
    }
}
