<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Exception\FileUploadException;
use App\Exception\InvalidFileTypeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductFileUploader
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly array $allowedFileTypes,
        private readonly LoggerInterface $logger
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        try {
            $newFileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $fileType = $this->detect($file->getClientOriginalName());

            $this->validateFileType($fileType);

            if (!is_dir($this->targetDirectory)) {
                mkdir($this->targetDirectory, 0755, true);
            }

            $file->move($this->targetDirectory, $newFileName);

            return $this->targetDirectory . \DIRECTORY_SEPARATOR . $newFileName;
        } catch (\Exception $e) {
            $this->logger->error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            throw new FileUploadException('File upload failed', 0, $e);
        }
    }

    private function validateFileType(string $fileType): void
    {
        if (!\in_array($fileType, $this->allowedFileTypes)) {
            throw new InvalidFileTypeException(\sprintf('The file type "%s" is not supported.', $fileType));
        }
    }

    private function detect(string $filename): string
    {
        return pathinfo($filename, \PATHINFO_EXTENSION);
    }
}
