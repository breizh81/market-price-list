<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Service\Messenger\SendEmailImportBatch\SendEmailMessage;
use App\Service\Mailer\Upload\UploadEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(private readonly UploadEmail $emailService)
    {
    }

    public function __invoke(SendEmailMessage $message)
    {
        $this->emailService->sendImportBatchCompletedEmail($message->getImportBatch());
    }
}

