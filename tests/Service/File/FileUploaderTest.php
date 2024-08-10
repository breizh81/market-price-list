<?php

namespace App\Tests\Service\File;

use App\Exception\FileUploadException;
use App\Exception\InvalidFileTypeException;
use App\Service\File\FileUploader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileUploaderTest tests functionality of the FileUploader class.
 */
class FileUploaderTest extends TestCase
{
    private $targetDirectory = '/tmp';
    private $allowedFileTypes = ['txt'];
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Tests upload method with valid file type.
     */
    public function testUploadWithValidFileType(): void
    {
        $fileMock = $this->createMock(UploadedFile::class);
        $fileMock->expects($this->any())->method('getClientOriginalExtension')->willReturn('txt');
        $fileMock->expects($this->any())->method('getClientOriginalName')->willReturn('test.txt');
        $fileMock->expects($this->any())->method('move')->willReturnCallback(function ($directory, $filename) {
            $this->assertSame($this->targetDirectory, $directory);
            $this->assertMatchesRegularExpression('/[a-z0-9]+\./i', $filename);
        });

        $uploader = new FileUploader($this->targetDirectory, $this->logger, $this->allowedFileTypes);
        $return = $uploader->upload($fileMock);

        $this->assertStringStartsWith($this->targetDirectory, $return);
    }

    /**
     * Tests upload method with an unsupported file type.
     */
    public function testUploadWithUnsupportedFileType(): void
    {
        $this->expectException(InvalidFileTypeException::class);

        $fileMock = $this->createMock(UploadedFile::class);
        $fileMock->expects($this->any())->method('getClientOriginalExtension')->willReturn('docx');
        $fileMock->expects($this->any())->method('getClientOriginalName')->willReturn('test.docx');

        $uploader = new FileUploader($this->targetDirectory, $this->logger, $this->allowedFileTypes);
        $uploader->upload($fileMock);
    }

    /**
     * Tests upload method in case of an error.
     */
    public function testUploadWithError(): void
    {
        $this->expectException(FileUploadException::class);

        $fileMock = $this->createMock(UploadedFile::class);
        $fileMock->expects($this->any())->method('getClientOriginalExtension')->willReturn('txt');
        $fileMock->expects($this->any())->method('getClientOriginalName')->willReturn('test.txt');
        $fileMock->expects($this->any())->method('move')->willThrowException(new \Exception());

        $uploader = new FileUploader($this->targetDirectory, $this->logger, $this->allowedFileTypes);
        $uploader->upload($fileMock);
    }
}
