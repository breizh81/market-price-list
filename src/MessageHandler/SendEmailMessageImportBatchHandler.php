<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Service\Mailer\Upload\UploadEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageImportBatchHandler
{
    public function __construct(private readonly UploadEmail $emailService)
    {
    }

    public function __invoke(SendEmailImportBatchMessage $message): void
    {
        $this->emailService->sendImportBatchCompletedEmail($message->getImportBatch());
    }
}
