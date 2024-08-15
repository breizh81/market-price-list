<?php

declare(strict_types=1);

namespace App\Tests\Service\File;

use App\Entity\Supplier;
use App\Exception\FileImporterException;
use App\Service\File\ProductFileImporter;
use App\Service\Importer\ProductImporter\ImporterInterface;
use PHPUnit\Framework\TestCase;

class ProductFileImporterTest extends TestCase
{
    private $importer;
    private $supplier;
    private $productFileImporter;

    protected function setUp(): void
    {
        $this->importer = $this->createMock(ImporterInterface::class);
        $this->supplier = $this->createMock(Supplier::class);
        $this->productFileImporter = new ProductFileImporter();
    }

    public function testSetImportersWithValidImporters()
    {
        $importers = [$this->importer];
        $this->productFileImporter->setImporters($importers);

        $this->assertSame($importers, $this->productFileImporter->getImporters());
    }

    public function testSetImportersWithInvalidImporter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All importers must implement ImporterInterface');

        $invalidImporter = new \stdClass();
        $this->productFileImporter->setImporters([$invalidImporter]);
    }

    public function testImportFileWithSupportedExtension()
    {
        $filePath = 'test.csv';
        $this->importer->method('supports')->with('csv')->willReturn(true);
        $this->importer->expects($this->once())->method('import')->with($filePath, $this->supplier);

        $this->productFileImporter->setImporters([$this->importer]);
        $this->productFileImporter->importFile($filePath, $this->supplier);
    }

    public function testImportFileWithUnsupportedExtension()
    {
        $filePath = 'test.txt';
        $this->importer->method('supports')->with('txt')->willReturn(false);

        $this->productFileImporter->setImporters([$this->importer]);

        $this->expectException(FileImporterException::class);
        $this->expectExceptionMessage('No importer found for extension "txt"');

        $this->productFileImporter->importFile($filePath, $this->supplier);
    }
}
