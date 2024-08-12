<?php
declare(strict_types=1);

namespace App\Service\Mailer\Upload;

use App\DTO\ImportBatchDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UploadEmail
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $senderEmail,
        private readonly string $recipientEmail
    ) {
    }

    public function sendImportBatchCompletedEmail(ImportBatchDTO $importBatchDTO): void
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($this->recipientEmail)
            ->subject('The import is completed')
            ->text('All products have been added');

        try {
            $this->mailer->send($email);
            $this->logger->debug('Updated import batch after message handling.', [
                'importBatchId' => $importBatchDTO->getId(),
                'sender' => $this->senderEmail,
                'recipient' => $this->recipientEmail,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send import batch completion email.', [
                'importBatchId' => $importBatchDTO->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
