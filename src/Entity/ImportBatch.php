<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'import_batch')]
class ImportBatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private int $id;

    #[ORM\Column(type: 'boolean')]
    private bool $isCompleted = false;

    #[ORM\Column(type: 'integer')]
    private int $totalMessages = 0;

    #[ORM\Column(type: 'integer')]
    private int $processedMessages = 0;

    public function markAsCompleted(): void
    {
        $this->isCompleted = true;
    }

    public function incrementProcessedMessages(): void
    {
        ++$this->processedMessages;
    }

    public function setTotalMessages(int $count): void
    {
        $this->totalMessages = $count;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function getProcessedMessages(): int
    {
        return $this->processedMessages;
    }

    public function getTotalMessages(): int
    {
        return $this->totalMessages;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
