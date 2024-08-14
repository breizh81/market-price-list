<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Supplier;
use App\Exception\FileImporterException;
use App\Service\Importer\ProductImporter\ImporterInterface;

class ProductFileImporter
{
    /**
     * @var ImporterInterface[] $importers
     */
    private array $importers = [];

    public function setImporters(iterable $importers): void
    {
        foreach ($importers as $importer) {
            if (!$importer instanceof ImporterInterface) {
                throw new \InvalidArgumentException('All importers must implement ImporterInterface');
            }
        }

        $this->importers = is_array($importers) ? $importers : iterator_to_array($importers);
    }

    public function importFile(string $filePath, Supplier $entity): void
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
