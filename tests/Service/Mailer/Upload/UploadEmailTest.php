<?php

declare(strict_types=1);

namespace App\Tests\Service\Mailer\Upload;

use App\DTO\ImportBatchDTO;
use App\Exception\UploadEmailException;
use App\Service\Mailer\Upload\UploadEmail;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UploadEmailTest extends TestCase
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $senderEmail;
    private string $recipientEmail;
    private UploadEmail $uploadEmail;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->senderEmail = 'sender@example.com';
        $this->recipientEmail = 'recipient@example.com';
        $this->uploadEmail = new UploadEmail($this->mailer, $this->logger, $this->senderEmail, $this->recipientEmail);
    }

    public function testSendImportBatchCompletedEmail()
    {
        $importBatchDTO = $this->createMock(ImportBatchDTO::class);
        $importBatchDTO->method('getId')->willReturn(1);

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(Email::class));

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Updated import batch after message handling.', [
                'importBatchId' => 1,
                'sender' => $this->senderEmail,
                'recipient' => $this->recipientEmail,
            ]);

        $this->uploadEmail->sendImportBatchCompletedEmail($importBatchDTO);
    }

    public function testSendImportBatchCompletedEmailWithException()
    {
        $importBatchDTO = $this->createMock(ImportBatchDTO::class);
        $importBatchDTO->method('getId')->willReturn(1);

        $this->mailer->method('send')
            ->willThrowException(new \Exception('Failed to send email'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to send import batch completion email.', [
                'importBatchId' => 1,
                'error' => 'Failed to send email',
            ]);

        $this->expectException(UploadEmailException::class);
        $this->expectExceptionMessage('Failed to send import batch completion email');

        $this->uploadEmail->sendImportBatchCompletedEmail($importBatchDTO);
    }
}
