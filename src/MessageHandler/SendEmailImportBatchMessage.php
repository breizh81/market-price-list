<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\DTO\ImportBatchDTO;

class SendEmailImportBatchMessage
{
    public function __construct(private readonly ImportBatchDTO $importBatchDTO)
    {
    }

    public function getImportBatch(): ImportBatchDTO
    {
        return $this->importBatchDTO;
    }
}
