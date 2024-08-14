<?php

declare(strict_types=1);

namespace App\Tests\Service\Importer\ProductImporter;

use App\DTO\ImportBatchDTO;
use App\DTO\ProductDTO;
use App\DTO\SupplierDTO;
use App\Entity\ImportBatch;
use App\Entity\Supplier;
use App\Factory\ImportBatchFactory;
use App\Service\Importer\ProductImporter\CsvImporter;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CsvImporterTest extends TestCase
{
    private MessageBusInterface $messenger;
    private ImportBatchFactory $importBatchFactory;
    private CsvImporter $csvImporter;
    private Supplier $supplier;
    private ImportBatch $importBatch;

    protected function setUp(): void
    {
        $this->messenger = $this->createMock(MessageBusInterface::class);
        $this->importBatchFactory = $this->createMock(ImportBatchFactory::class);
        $this->csvImporter = new CsvImporter($this->messenger, $this->importBatchFactory);
        $this->supplier = $this->createMock(Supplier::class);
        $this->importBatch = $this->createMock(ImportBatch::class);
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->csvImporter->supports('csv'));
        $this->assertFalse($this->csvImporter->supports('txt'));
    }

    public function testImport(): void
    {
        $importBatchDTO = new ImportBatchDTO(1, 1, 0, false);
        $supplierDTO = new SupplierDTO('FRUITS', 1);
        $productDTO = new ProductDTO('kiwi', 'kiwi', 1.2, $supplierDTO, $importBatchDTO);

        $filePath = __DIR__ . '/test.csv';

        $this->importBatchFactory
            ->expects($this->once())
            ->method('createAndSave')
            ->willReturn($this->importBatch);

        $this->importBatchFactory
            ->expects($this->once())
            ->method('save')
            ->with($this->importBatch);

        $message = new InsertProductMessage($productDTO, 1);
        $envelope = new Envelope($message);

        $this->messenger
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($this->isInstanceOf(InsertProductMessage::class))
            ->willReturn($envelope);

        $this->csvImporter->import($filePath, $this->supplier);

        $this->assertEquals(2, $this->importBatch->getTotalMessages());
    }
}
