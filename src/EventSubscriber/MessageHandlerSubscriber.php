<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DTO\ImportBatchDTO;
use App\MessageHandler\SendEmailImportBatchMessage;
use App\Repository\ImportBatchRepository;
use App\Service\Messenger\InsertProduct\InsertProductMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageHandlerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ImportBatchRepository $importBatchRepository,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
            WorkerMessageHandledEvent::class => 'onMessageHandled',
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof InsertProductMessage) {
            $this->logger->debug('Message received for processing.', [
                'import_batch_id' => $message->getImportBatchId(),
            ]);

            $importBatch = $this->importBatchRepository->find($message->getImportBatchId());
            if ($importBatch) {
                $this->importBatchRepository->save($importBatch);
                $this->logger->debug('Incremented pending messages count for import batch.', [
                    'import_batch_id' => $message->getImportBatchId(),
                ]);
            }
        }
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof InsertProductMessage) {
            $this->finalizeMessageProcessing($message);
        }
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof InsertProductMessage) {
            $this->logger->error('Message processing failed.', [
                'import_batch_id' => $message->getImportBatchId(),
                'error' => $event->getThrowable()->getMessage(),
            ]);
            $this->finalizeMessageProcessing($message);
        }
    }

    private function finalizeMessageProcessing(InsertProductMessage $message): void
    {
        $importBatchId = $message->getImportBatchId();
        $importBatch = $this->importBatchRepository->find($importBatchId);

        if ($importBatch) {
            $this->logger->debug('Import batch before ProcessedMessages', [
                'import_batch_id' => $importBatchId,
                'total_count' => $importBatch->getTotalMessages(),
                'processed_count' => $importBatch->getProcessedMessages(),
            ]);

            $importBatch->incrementProcessedMessages();

            $this->logger->debug('Import batch after ProcessedMessages', [
                'import_batch_id' => $importBatchId,
                'total_count' => $importBatch->getTotalMessages(),
                'processed_count' => $importBatch->getProcessedMessages(),
            ]);

            if ($importBatch->getProcessedMessages() === $importBatch->getTotalMessages()) {
                $importBatch->markAsCompleted();
                $importBatchDTO = ImportBatchDTO::fromEntity($importBatch);
                $this->dispatchCompletionNotification($importBatchDTO);
            }
            $this->importBatchRepository->save($importBatch);
            $this->logger->debug('Updated import batch after message handling.', [
                'import_batch_id' => $importBatchId,
                'total_count' => $importBatch->getTotalMessages(),
                'processed_count' => $importBatch->getProcessedMessages(),
                'status' => $importBatch->isCompleted() ? 'completed' : 'in progress',
            ]);
        }
    }

    private function dispatchCompletionNotification(ImportBatchDTO $importBatchDTO): void
    {
        $this->messageBus->dispatch(new SendEmailImportBatchMessage($importBatchDTO));
    }
}
