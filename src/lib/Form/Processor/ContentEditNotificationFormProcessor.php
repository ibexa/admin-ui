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
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentEditNotificationFormProcessor implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var array */
    private $siteAccessGroups;

    /**
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param array $siteAccessGroups
     */
    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        RequestStack $requestStack,
        array $siteAccessGroups
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->requestStack = $requestStack;
        $this->siteAccessGroups = $siteAccessGroups;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['addPublishMessage', 5],
            ContentFormEvents::CONTENT_SAVE_DRAFT => ['addSaveDraftMessage', 5],
        ];
    }

    /**
     * @param \Ibexa\ContentForms\Event\FormActionEvent $event
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function addPublishMessage(FormActionEvent $event)
    {
        if (!$this->isAdminSiteAccess($this->requestStack->getCurrentRequest())) {
            return;
        }
        $this->notificationHandler->success(
            /** @Desc("Content published.") */
            'content.published.success',
            [],
            'ibexa_content_edit'
        );
    }

    /**
     * @param \Ibexa\ContentForms\Event\FormActionEvent $event
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function addSaveDraftMessage(FormActionEvent $event)
    {
        if (!$this->isAdminSiteAccess($this->requestStack->getCurrentRequest())) {
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    protected function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'));
    }
}
