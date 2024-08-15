<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\ImportBatch;
use App\EventSubscriber\MessageHandlerSubscriber;
use App\Repository\ImportBatchRepository;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageHandlerSubscriberTest extends TestCase
{
    private MessageHandlerSubscriber $subscriber;
    private MockObject $importBatchRepository;
    private MockObject $logger;
    private MockObject $messageBus;

    protected function setUp(): void
    {
        $this->importBatchRepository = $this->createMock(ImportBatchRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->subscriber = new MessageHandlerSubscriber(
            $this->importBatchRepository,
            $this->logger,
            $this->messageBus
        );
    }

    public function testOnMessageHandled(): void
    {
        $insertProductMessage = $this->createMock(InsertProductMessage::class);
        $insertProductMessage->method('getImportBatchId')->willReturn(1);

        $importBatch = $this->createMock(ImportBatch::class);
        $importBatch->method('getProcessedMessages')->willReturn(1);
        $importBatch->method('getTotalMessages')->willReturn(2);

        $this->importBatchRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($importBatch);

        $this->importBatchRepository->expects($this->once())
            ->method('save')
            ->with($importBatch);

        $logMessages = [];
        $this->logger->method('debug')->willReturnCallback(function ($message, $context) use (&$logMessages) {
            $logMessages[] = ['message' => $message, 'context' => $context];
        });

        $envelope = new Envelope($insertProductMessage);
        $event = new WorkerMessageHandledEvent($envelope, 'test_receiver');

        $this->subscriber->onMessageHandled($event);

        $this->assertCount(3, $logMessages);
        $this->assertEquals('Import batch before ProcessedMessages', $logMessages[0]['message']);
        $this->assertEquals('Import batch after ProcessedMessages', $logMessages[1]['message']);
        $this->assertEquals('Updated import batch after message handling.', $logMessages[2]['message']);
    }

    public function testOnMessageFailed(): void
    {
        // Mock InsertProductMessage
        $insertProductMessage = $this->createMock(InsertProductMessage::class);
        $insertProductMessage->method('getImportBatchId')->willReturn(1);

        // Mock ImportBatch
        $importBatch = $this->createMock(ImportBatch::class);
        $importBatch->method('getProcessedMessages')->willReturn(1);
        $importBatch->method('getTotalMessages')->willReturn(2);

        // Use a real exception for simulating failure
        $exception = new \Exception('An error occurred.');

        // Set up repository and logger expectations
        $this->importBatchRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($importBatch);

        $this->importBatchRepository->expects($this->once())
            ->method('save')
            ->with($importBatch);

        // Capture log messages
        $logMessages = [];
        $this->logger->method('error')->willReturnCallback(function ($message, $context) use (&$logMessages) {
            $logMessages[] = ['message' => $message, 'context' => $context];
        });

        $this->logger->method('debug')->willReturnCallback(function ($message, $context) use (&$logMessages) {
            $logMessages[] = ['message' => $message, 'context' => $context];
        });

        // Create event and envelope
        $envelope = new Envelope($insertProductMessage);
        $event = new WorkerMessageFailedEvent($envelope, 'test_receiver', $exception);

        // Call the method to be tested
        $this->subscriber->onMessageFailed($event);

        // Assertions
        $this->assertCount(4, $logMessages);
        $this->assertEquals('Message processing failed.', $logMessages[0]['message']);
        $this->assertEquals('An error occurred.', $logMessages[0]['context']['error']);
        $this->assertEquals('Import batch before ProcessedMessages', $logMessages[1]['message']);
        $this->assertEquals('Import batch after ProcessedMessages', $logMessages[2]['message']);
        $this->assertEquals('Updated import batch after message handling.', $logMessages[3]['message']);
    }
}
