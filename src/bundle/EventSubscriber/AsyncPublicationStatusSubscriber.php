<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\EventSubscriber;

use Ibexa\Bundle\Core\Message\PublishContentAsync;
use Ibexa\Core\Repository\ContentService\AsyncPublicationService;
use Ibexa\Mercure\Publisher\MercurePublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * Single orchestration point for the async content publication lifecycle: reacting to the Symfony
 * Messenger events around a {@see PublishContentAsync} message, each handler both updates the backend
 * job store (via {@see AsyncPublicationService}) and notifies the AdminUI via Mercure, so the Versions
 * tab can refresh the per-version publication badge live, without a page reload.
 *
 * The "completed" status has no backend counterpart (the job row is removed on success) and is faked
 * purely on the UI.
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
        private readonly AsyncPublicationService $asyncPublicationService,
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
        $envelope = $event->getEnvelope();
        if (!$this->isPublishContentAsyncMessage($envelope)) {
            return;
        }

        /** @var PublishContentAsync $message */
        $message = $envelope->getMessage();

        // The job is already recorded as queued by AsyncPublicationService::registerPublication();
        // here we only notify the UI.
        $this->publishStatus($message, self::STATUS_QUEUED, true);
    }

    public function onProcessing(WorkerMessageReceivedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        if (!$this->isPublishContentAsyncMessage($envelope)) {
            return;
        }

        /** @var PublishContentAsync $message */
        $message = $envelope->getMessage();

        /**
         * open question: should job status updating be here?:
         * - coupled with publishing status to frontend
         * - or as separated subscriber
         * - or part of \Ibexa\Core\Repository\ContentService\AsyncPublicationService
         */
        $this->asyncPublicationService->markProcessing($message->contentId);
        $this->publishStatus($message, self::STATUS_PROCESSING);
    }

    public function onCompleted(WorkerMessageHandledEvent $event): void
    {
        $envelope = $event->getEnvelope();
        if (!$this->isPublishContentAsyncMessage($envelope)) {
            return;
        }

        /** @var PublishContentAsync $message */
        $message = $envelope->getMessage();

        // The new published version now exists; clearing the job clears the AdminUI "in progress"
        // indicator. "completed" is faked purely on the UI.
        $this->asyncPublicationService->markCompleted($message->contentId);
        $this->publishStatus($message, self::STATUS_COMPLETED);
        $this->publishCompletedNotification($message);
    }

    public function onFailed(WorkerMessageFailedEvent $event): void
    {
        // Stay "processing" while Messenger will still retry the message.
        if ($event->willRetry()) {
            return;
        }

        $envelope = $event->getEnvelope();
        if (!$this->isPublishContentAsyncMessage($envelope)) {
            return;
        }

        /** @var PublishContentAsync $message */
        $message = $envelope->getMessage();

        $this->asyncPublicationService->markFailed($message->contentId, $event->getThrowable()->getMessage());
        $this->publishStatus($message, self::STATUS_FAILED);
    }

    private function publishStatus(PublishContentAsync $message, string $status, bool $deffered = false): void
    {
        try {
            $topic = sprintf(self::TOPIC_TEMPLATE, $message->contentId);
            $data = [
                'contentId' => $message->contentId,
                'versionNo' => $message->versionNo,
                'status' => $status,
            ];

            $deffered
                ? $this->publisher->publishDeferred($topic, $data, self::EVENT_TYPE)
                : $this->publisher->publish($topic, $data, self::EVENT_TYPE);
        } catch (\Throwable $e) {
            $this->logger->error('Mercure: failed to publish async publication status: {error}', [
                'error' => $e->getMessage(),
                'contentId' => $message->contentId,
                'versionNo' => $message->versionNo,
                'status' => $status,
            ]);
        }
    }

    public function publishCompletedNotification(PublishContentAsync $message): void
    {
        $contentId = $message->contentId;

        try {
            $this->publisher->publish(sprintf('/async-publication/%d', $contentId), [
                'versionNo' => $message->versionNo,
            ], 'async_version_published');
        } catch (\Throwable $e) {
            $this->logger->error('Mercure: failed to publish async_version_published notification: {error}', [
                'error' => $e->getMessage(),
                'contentId' => $contentId,
            ]);
        }
    }

    private function isPublishContentAsyncMessage(Envelope $envelope): bool
    {
        return $envelope->getMessage() instanceof PublishContentAsync;
    }
}
