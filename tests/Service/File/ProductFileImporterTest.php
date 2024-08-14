<?php

declare(strict_types=1);

namespace App\Tests\Service\File;

use App\Service\File\ProductFileImporter;
use PHPUnit\Framework\TestCase;

class ProductFileImporterTest extends TestCase
{
    private $importer;
    private $fileImporter;

    protected function setUp(): void
    {
        $this->importer = $this->createMock('App\Service\File\ImporterInterface');
        $this->fileImporter = new ProductFileImporter();
        $this->fileImporter->setImporters([$this->importer]);
    }

    /**
     * @dataProvider providerTestImportFile
     */
    public function testImportFile(
        bool $returnValue,
        string $importFunctionCall,
        bool $shouldThrowException,
        string $importFile
    ): void {
        $this->importer->expects($this->once())
            ->method('supports')
            ->with('csv')
            ->willReturn($returnValue);

        $this->importer->expects($this->$importFunctionCall())->method('import')->with($importFile);

        if ($shouldThrowException) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('No importer found for extension "csv"');
        }

        // Exercise SUT
        $this->fileImporter->importFile($importFile);
    }

    public function providerTestImportFile(): \Generator
    {
        yield 'Supported File' => [
            'returnValue' => true,
            'importFunctionCall' => 'once',
            'shouldThrowException' => false,
            'importFile' => './test.csv',
        ];

        yield 'Unsupported File' => [
            'returnValue' => false,
            'importFunctionCall' => 'never',
            'shouldThrowException' => true,
            'importFile' => './test.csv',
        ];
    }
}
