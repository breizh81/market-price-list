<?php
declare(strict_types=1);

namespace App\Service\Messenger\SendEmailImportBatch;

use App\DTO\ImportBatchDTO;

class SendEmailMessage
{
    public function __construct(private readonly ImportBatchDTO $importBatchDTO)
    {
    }

    public function getImportBatch(): ImportBatchDTO
    {
        return $this->importBatchDTO;
    }
}
