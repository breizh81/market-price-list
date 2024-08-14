<?php

namespace App\Tests\EventSubscriber;

use App\DTO\ImportBatchDTO;
use App\Entity\ImportBatch;
use App\EventSubscriber\MessageHandlerSubscriber;
use App\MessageHandler\SendEmailImportBatchMessage;
use App\Repository\ImportBatchRepository;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
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

    public function testImplementsEventSubscriberInterface(): void
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->subscriber);
    }

    public function testOnMessageReceived(): void
    {
        $message = $this->createMock(InsertProductMessage::class);
        $message->method('getImportBatchId')->willReturn(1);

        $importBatch = $this->createMock(ImportBatch::class);

        $this->importBatchRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($importBatch);

        $this->importBatchRepository
            ->expects($this->once())
            ->method('save')
            ->with($importBatch);

        $this->logger
            ->expects($this->exactly(2))
            ->method('debug');

        $envelope = new Envelope($message);
        $event = new WorkerMessageReceivedEvent($envelope, 'default');

        $this->subscriber->onMessageReceived($event);
    }

    public function testOnMessageHandled(): void
    {
        $message = $this->createMock(InsertProductMessage::class);
        $message->method('getImportBatchId')->willReturn(1);

        // Instantiate the entity directly
        $importBatch = new ImportBatch();
        $importBatch->setTotalMessages(1); // Set necessary properties

        // Ensure the repository returns the actual ImportBatch entity
        $this->importBatchRepository
            ->method('find')
            ->with(1)
            ->willReturn($importBatch);

        $this->logger
            ->expects($this->exactly(3))
            ->method('debug');

        // Create the DTO from the entity
        $importBatchDTO = ImportBatchDTO::fromEntity($importBatch);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SendEmailImportBatchMessage $message) use ($importBatchDTO) {
                return $message->getImportBatch() === $importBatchDTO;
            }));

        $this->importBatchRepository
            ->expects($this->once())
            ->method('save')
            ->with($importBatch);

        $envelope = new Envelope($message);
        $event = new WorkerMessageHandledEvent($envelope, 'default');

        $this->subscriber->onMessageHandled($event);

        // Verify the entity state after processing
        $this->assertEquals(1, $importBatch->getProcessedMessages());
        $this->assertTrue($importBatch->isCompleted());
    }

    public function testOnMessageFailed(): void
    {
        $message = $this->createMock(InsertProductMessage::class);
        $message->method('getImportBatchId')->willReturn(1);

        $importBatch = $this->createMock(ImportBatch::class);

        $this->importBatchRepository
            ->method('find')
            ->with(1)
            ->willReturn($importBatch);

        $importBatch
            ->expects($this->once())
            ->method('incrementProcessedMessages');

        $importBatch
            ->expects($this->once())
            ->method('getProcessedMessages')
            ->willReturn(1);

        $importBatch
            ->expects($this->once())
            ->method('getTotalMessages')
            ->willReturn(1);

        $importBatch
            ->expects($this->once())
            ->method('markAsCompleted');

        $this->importBatchRepository
            ->expects($this->once())
            ->method('save')
            ->with($importBatch);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Message processing failed.', $this->arrayHasKey('error'));

        $this->logger
            ->expects($this->exactly(3))
            ->method('debug');

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SendEmailImportBatchMessage::class));

        $envelope = new Envelope($message);
        $throwable = new \Exception('Test exception');
        $event = new WorkerMessageFailedEvent($envelope, 'default', $throwable);

        $this->subscriber->onMessageFailed($event);
    }
}
