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

        $this->importBatch
            ->method('getTotalMessages')
            ->willReturn(0); // Default return value for the getTotalMessages method
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->csvImporter->supports('csv'));
        $this->assertFalse($this->csvImporter->supports('txt'));
    }

    public function testImport(): void
    {
        $tempFile = tmpfile();
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        $csvContent = 'kiwi,kiwi,1.2';
        file_put_contents($tempFilePath, $csvContent);

        $importBatchDTO = new ImportBatchDTO(1, 1, 0, false);
        $supplierDTO = new SupplierDTO('FRUITS', 1);
        $productDTO = new ProductDTO('kiwi', 'kiwi', 1.2, $supplierDTO, $importBatchDTO);

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
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(InsertProductMessage::class))
            ->willReturn($envelope);

        $this->csvImporter->import($tempFilePath, $this->supplier);

        $this->assertEquals(0, $this->importBatch->getTotalMessages());

        fclose($tempFile);
    }
}
