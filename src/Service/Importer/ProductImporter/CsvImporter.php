<?php

declare(strict_types=1);

namespace App\Service\Importer\ProductImporter;

use App\DTO\ProductDTO;
use App\DTO\SupplierDTO;
use App\Entity\IdentifiableEntityInterface;
use App\Entity\Supplier;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class CsvImporter implements ImporterInterface
{
    const FILE_EXTENSION = 'csv';

    public function __construct(private readonly MessageBusInterface $messenger)
    {
    }

    public function supports(string $fileExtension): bool
    {
        return self::FILE_EXTENSION === $fileExtension;
    }

    public function import(string $filePath, IdentifiableEntityInterface $supplier): void
    {
        if (!$supplier instanceof Supplier) {
            throw new \InvalidArgumentException(\sprintf('Expected supplier of type Supplier, %s given', $supplier::class));
        }

        $file = new \SplFileObject($filePath, 'r');

        while (!$file->eof()) {
            $data = $file->fgetcsv();

            if (!\is_array($data)) {
                continue;
            }

            $data['supplier'] = SupplierDTO::fromEntity($supplier);

            $productDto = ProductDTO::fromArray($data);

            $this->messenger->dispatch(new InsertProductMessage($productDto));
        }
    }
}
