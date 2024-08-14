<?php

declare(strict_types=1);

namespace App\Tests\Service\File;

use App\Exception\FileUploadException;
use App\Service\File\ProductFileUploader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductFileUploaderTest extends TestCase
{
    private string $targetDirectory;
    private array $allowedFileTypes;
    private LoggerInterface $logger;
    private ProductFileUploader $uploader;

    protected function setUp(): void
    {
        $this->targetDirectory = '/tmp/uploads';
        $this->allowedFileTypes = ['csv'];
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uploader = new ProductFileUploader($this->targetDirectory, $this->allowedFileTypes, $this->logger);
    }

    public function testUploadWithValidFile(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('csv');
        $file->method('getClientOriginalName')->willReturn('test.csv');
        $file->expects($this->once())->method('move')->with($this->targetDirectory, 'test.csv');

        $result = $this->uploader->upload($file);

        $this->assertStringStartsWith($this->targetDirectory, $result);
    }

    public function testUploadWithInvalidFileType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('exe');
        $file->method('getClientOriginalName')->willReturn('test.exe');

        $this->expectException(FileUploadException::class);
        $this->expectExceptionMessage('File upload failed due to invalid file type.');

        $this->uploader->upload($file);
    }

    public function testUploadFileTypeInvalid(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('txt');
        $file->method('getClientOriginalName')->willReturn('test.txt');
        $file->method('move')->will($this->throwException(new \Exception('Move failed')));

        $this->logger->expects($this->once())->method('error')->with('File upload failed', [
            'error' => 'The file type "txt" is not supported.',
            'file' => 'test.txt',
        ]);

        $this->expectException(FileUploadException::class);
        $this->expectExceptionMessage('File upload failed');

        $this->uploader->upload($file);
    }

    public function testUploadWithException(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('csv');
        $file->method('getClientOriginalName')->willReturn('test.csv');
        $file->method('move')->will($this->throwException(new \Exception('Move failed')));

        $this->logger->expects($this->once())->method('error')->with('File upload failed', [
            'error' => 'Move failed',
            'file' => 'test.csv',
        ]);

        $this->expectException(FileUploadException::class);
        $this->expectExceptionMessage('File upload failed');

        $this->uploader->upload($file);
    }
}
