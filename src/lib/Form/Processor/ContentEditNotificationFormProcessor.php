<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class ContentEditNotificationFormProcessor implements EventSubscriberInterface
{
    /**
     * @param array<string, string[]> $siteAccessGroups
     */
    public function __construct(
        private TranslatableNotificationHandlerInterface $notificationHandler,
        private RequestStack $requestStack,
        private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
        private array $siteAccessGroups
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['addPublishMessage', 5],
            ContentFormEvents::CONTENT_SAVE_DRAFT => ['addSaveDraftMessage', 5],
        ];
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function addPublishMessage(FormActionEvent $event): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return;
        }

        if (!$this->isAdminSiteAccess($currentRequest)) {
            return;
        }

        if ($this->isAsyncContentPublishEnabled()) {
            $this->notificationHandler->success(
                /** @Desc("Content publishing started.") */
                'content.publish.started',
                [],
                'ibexa_content_edit'
            );
        } else {
            $this->notificationHandler->success(
                /** @Desc("Content published.") */
                'content.published.success',
                [],
                'ibexa_content_edit'
            );
        }
    }

    private function isAsyncContentPublishEnabled(): bool
    {
        return (bool) ($this->repositoryConfigurationProvider->getRepositoryConfig()['async_content_publish'] ?? false);
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function addSaveDraftMessage(FormActionEvent $event): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return;
        }

        if (!$this->isAdminSiteAccess($currentRequest)) {
            return;
        }

        $this->notificationHandler->success(
            /** @Desc("Content draft saved.") */
            'content.draft_saved.success',
            [],
            'ibexa_content_edit'
        );
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy(
            $request->attributes->get('siteaccess')
        );
    }
}
