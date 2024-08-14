<?php

declare(strict_types=1);

namespace App\Service\Importer\ProductImporter;

use App\DTO\ImportBatchDTO;
use App\DTO\ProductDTO;
use App\DTO\SupplierDTO;
use App\Entity\Supplier;
use App\Factory\ImportBatchFactory;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class CsvImporter implements ImporterInterface
{
    private const FILE_EXTENSION = 'csv';

    public function __construct(
        private readonly MessageBusInterface $messenger,
        private readonly ImportBatchFactory $importBatchFactory
    ) {
    }

    public function supports(string $fileExtension): bool
    {
        return self::FILE_EXTENSION === $fileExtension;
    }

    public function import(string $filePath, Supplier $supplier): void
    {
        $file = new \SplFileObject($filePath, 'r');
        $importBatch = $this->importBatchFactory->createAndSave();
        $importBatchDTO = ImportBatchDTO::fromEntity($importBatch);

        $importBatch->setTotalMessages(0);

        // First pass to count the total number of messages
        while (!$file->eof()) {
            $data = $file->fgetcsv();
            if (!\is_array($data) || empty($data)) {
                continue;
            }

            $importBatch->setTotalMessages($importBatch->getTotalMessages() + 1);
        }

        $this->importBatchFactory->save($importBatch);

        // Second pass to dispatch messages
        $file->rewind();
        while (!$file->eof()) {
            $data = $file->fgetcsv();
            if (!\is_array($data) || empty($data)) {
                continue;
            }

            $data['supplierDTO'] = SupplierDTO::fromEntity($supplier);
            $data['importBatchDTO'] = $importBatchDTO;

            $productDto = ProductDTO::fromArray($data);
            $this->messenger->dispatch(new InsertProductMessage($productDto, $importBatch->getId()));
        }
    }
}
