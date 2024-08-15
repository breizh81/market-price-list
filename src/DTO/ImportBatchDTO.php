<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\ImportBatch;

class ImportBatchDTO
{
    private int $id;
    private int $totalMessages;
    private int $processedMessages;
    private bool $isCompleted;

    public function __construct(int $id, int $totalMessages, int $processedMessages, bool $isCompleted)
    {
        $this->id = $id;
        $this->totalMessages = $totalMessages;
        $this->processedMessages = $processedMessages;
        $this->isCompleted = $isCompleted;
    }

    public static function fromEntity(ImportBatch $importBatch): self
    {
        return new self(
            $importBatch->getId(),
            $importBatch->getTotalMessages(),
            $importBatch->getProcessedMessages(),
            $importBatch->isCompleted()
        );
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array<string, int|bool> the associative array representation of the DTO
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'totalMessages' => $this->totalMessages,
            'processedMessages' => $this->processedMessages,
            'isCompleted' => $this->isCompleted,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTotalMessages(): int
    {
        return $this->totalMessages;
    }

    public function getProcessedMessages(): int
    {
        return $this->processedMessages;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }
}
