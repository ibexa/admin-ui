<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\EventSubscriber;

use Ibexa\Bundle\Core\Message\PublishContentAsync;
use Ibexa\Mercure\Publisher\MercurePublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * Pushes async content publication status changes to the Mercure hub so the AdminUI Versions tab can
 * refresh the per-version publication badge live, without a page reload.
 *
 * Mirrors {@see \Ibexa\Bundle\Core\EventSubscriber\PublishContentAsyncFailureSubscriber}: the backend
 * job store remains the source of truth, this only emits UI notifications. The "completed" status has
 * no backend counterpart (the job row is removed on success) and is faked purely on the UI.
 */
final class AsyncPublicationStatusSubscriber implements EventSubscriberInterface
{
    private const string TOPIC_TEMPLATE = '/async-publication/%d';
    private const string EVENT_TYPE = 'async_publication_status';

    private const string STATUS_QUEUED = 'queued';
    private const string STATUS_PROCESSING = 'processing';
    private const string STATUS_COMPLETED = 'completed';
    private const string STATUS_FAILED = 'failed';

    public function __construct(
        private readonly MercurePublisher $publisher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => 'onQueued',
            WorkerMessageReceivedEvent::class => 'onProcessing',
            WorkerMessageHandledEvent::class => 'onCompleted',
            WorkerMessageFailedEvent::class => 'onFailed',
        ];
    }

    public function onQueued(SendMessageToTransportsEvent $event): void
    {
        $this->publishStatus($event->getEnvelope(), self::STATUS_QUEUED);
    }

    public function onProcessing(WorkerMessageReceivedEvent $event): void
    {
        $this->publishStatus($event->getEnvelope(), self::STATUS_PROCESSING);
    }

    public function onCompleted(WorkerMessageHandledEvent $event): void
    {
        $this->publishStatus($event->getEnvelope(), self::STATUS_COMPLETED);
    }

    public function onFailed(WorkerMessageFailedEvent $event): void
    {
        // Stay "processing" while Messenger will still retry the message.
        if ($event->willRetry()) {
            return;
        }

        $this->publishStatus($event->getEnvelope(), self::STATUS_FAILED);
    }

    private function publishStatus(Envelope $envelope, string $status): void
    {
        $message = $envelope->getMessage();

        if (!$message instanceof PublishContentAsync) {
            return;
        }

        try {
            $this->publisher->publish(
                sprintf(self::TOPIC_TEMPLATE, $message->contentId),
                [
                    'contentId' => $message->contentId,
                    'versionNo' => $message->versionNo,
                    'status' => $status,
                ],
                self::EVENT_TYPE,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Mercure: failed to publish async publication status: {error}', [
                'error' => $e->getMessage(),
                'contentId' => $message->contentId,
                'versionNo' => $message->versionNo,
                'status' => $status,
            ]);
        }
    }

    private function isPublishContentAsyncMessage(Envelope $envelope): bool
    {

    }
}
