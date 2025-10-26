<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
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
    /** @var TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var RequestStack */
    private $requestStack;

    /** @var array */
    private $siteAccessGroups;

    /**
     * @param TranslatableNotificationHandlerInterface $notificationHandler
     * @param RequestStack $requestStack
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
     * @param FormActionEvent $event
     *
     * @throws InvalidArgumentException
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
     * @param FormActionEvent $event
     *
     * @throws InvalidArgumentException
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
     * @param Request $request
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    protected function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'));
    }
}

class_alias(ContentEditNotificationFormProcessor::class, 'EzSystems\EzPlatformAdminUi\Form\Processor\ContentEditNotificationFormProcessor');
