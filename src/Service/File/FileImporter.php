<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\IdentifiableEntityInterface;
use App\Exception\FileImporterException;

class FileImporter
{
    private $importers;

    public function setImporters(iterable $importers): void
    {
        $this->importers = $importers;
    }

    public function importFile(string $filePath, ?IdentifiableEntityInterface $entity = null): void
    {
        $extension = pathinfo($filePath, \PATHINFO_EXTENSION);

        foreach ($this->importers as $importer) {
            if ($importer->supports($extension)) {
                $importer->import($filePath, $entity);

                return;
            }
        }

        throw new FileImporterException(\sprintf('No importer found for extension "%s"', $extension));
    }
}
