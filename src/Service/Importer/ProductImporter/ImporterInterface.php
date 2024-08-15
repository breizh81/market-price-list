<?php

declare(strict_types=1);

namespace App\Service\Importer\ProductImporter;

use App\Entity\Supplier;

interface ImporterInterface
{
    public function supports(string $fileExtension): bool;

    public function import(string $filePath, Supplier $supplier): void;
}
