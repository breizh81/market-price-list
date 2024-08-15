<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\DTO\ImportBatchDTO;
use App\MessageHandler\SendEmailImportBatchMessage;
use App\MessageHandler\SendEmailImportBatchMessageHandler;
use App\Service\Mailer\Upload\UploadEmail;
use PHPUnit\Framework\TestCase;

class SendEmailImportBatchMessageHandlerTest extends TestCase
{
    private UploadEmail $emailService;
    private SendEmailImportBatchMessageHandler $handler;

    protected function setUp(): void
    {
        $this->emailService = $this->createMock(UploadEmail::class);
        $this->handler = new SendEmailImportBatchMessageHandler($this->emailService);
    }

    public function testInvoke(): void
    {
        $importBatchDTO = $this->createMock(ImportBatchDTO::class);
        $message = $this->createMock(SendEmailImportBatchMessage::class);
        $message->method('getImportBatch')->willReturn($importBatchDTO);

        $this->emailService->expects($this->once())
            ->method('sendImportBatchCompletedEmail')
            ->with($importBatchDTO);

        ($this->handler)($message);
    }
}
