<?php

declare(strict_types=1);

namespace App\Service\Importer\ProductImporter;

use App\Entity\IdentifiableEntityInterface;

interface ImporterInterface
{
    public function supports(string $fileExtension): bool;

    public function import(string $filePath, IdentifiableEntityInterface $supplier): void;
}
