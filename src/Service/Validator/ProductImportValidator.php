<?php

declare(strict_types=1);

namespace App\Service\Validator;

use App\Exception\ProductImportException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Supplier;
use Doctrine\ORM\EntityManagerInterface;

class ProductImportValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validateFile($file): UploadedFile
    {
        if (!$file instanceof UploadedFile) {
            throw new ProductImportException('Invalid file type');
        }

        return $file;
    }

    public function validateSupplier(int $supplierId): Supplier
    {
        $supplier = $this->entityManager->getRepository(Supplier::class)->find($supplierId);

        if (!$supplier) {
            throw new ProductImportException('Supplier not found');
        }

        return $supplier;
    }
}
