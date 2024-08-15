<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\ImportBatch;
use Doctrine\ORM\EntityManagerInterface;

class ImportBatchFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createAndSave(): ImportBatch
    {
        $importBatch = new ImportBatch();
        $this->entityManager->persist($importBatch);
        $this->entityManager->flush();

        return $importBatch;
    }

    public function save(ImportBatch $importBatch): void
    {
        $this->entityManager->persist($importBatch);
        $this->entityManager->flush();
    }
}
