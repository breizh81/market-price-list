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
            $extension = $file->getClientOriginalExtension();
            $this->validateFileType($extension);

            if (!is_dir($this->targetDirectory)) {
                mkdir($this->targetDirectory, 0755, true);
            }

            $file->move($this->targetDirectory, $file->getClientOriginalName());
        } catch (InvalidFileTypeException $e) {
            $this->logger->error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            throw new FileUploadException('File upload failed due to invalid file type.', 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            throw new FileUploadException('File upload failed', 0, $e);
        }

        return $this->targetDirectory . \DIRECTORY_SEPARATOR . $file->getClientOriginalName();
    }

    private function validateFileType(string $extension): void
    {
        if (!\in_array($extension, $this->allowedFileTypes, true)) {
            throw new InvalidFileTypeException(\sprintf('The file type "%s" is not supported.', $extension));
        }
    }
}
