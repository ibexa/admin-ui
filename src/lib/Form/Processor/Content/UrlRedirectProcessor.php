<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\Content;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\SystemUrlRedirectProcessor;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UrlRedirectProcessor implements EventSubscriberInterface
{
    /** @var SiteAccess */
    private $siteaccess;

    /** @var SystemUrlRedirectProcessor */
    private $systemUrlRedirectProcessor;

    /** @var array */
    private $siteaccessGroups;

    /**
     * @param SiteAccess $siteaccess
     * @param SystemUrlRedirectProcessor $systemUrlRedirectProcessor
     * @param array $siteaccessGroups
     */
    public function __construct(
        SiteAccess $siteaccess,
        SystemUrlRedirectProcessor $systemUrlRedirectProcessor,
        array $siteaccessGroups
    ) {
        $this->siteaccess = $siteaccess;
        $this->systemUrlRedirectProcessor = $systemUrlRedirectProcessor;
        $this->siteaccessGroups = $siteaccessGroups;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['processRedirectAfterPublish', 0],
            ContentFormEvents::CONTENT_CANCEL => ['processRedirectAfterCancel', 0],
        ];
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function processRedirectAfterPublish(FormActionEvent $event): void
    {
        if ($event->getForm()['redirectUrlAfterPublish']->getData()) {
            return;
        }

        if ($this->isAdminSiteaccess()) {
            return;
        }

        $this->systemUrlRedirectProcessor->processRedirectAfterPublish($event);
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function processRedirectAfterCancel(FormActionEvent $event): void
    {
        if ($this->isAdminSiteaccess()) {
            return;
        }

        $this->systemUrlRedirectProcessor->processRedirectAfterCancel($event);
    }

    /**
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    protected function isAdminSiteaccess(): bool
    {
        return (new IsAdmin($this->siteaccessGroups))->isSatisfiedBy($this->siteaccess);
    }
}

class_alias(UrlRedirectProcessor::class, 'EzSystems\EzPlatformAdminUi\Form\Processor\Content\UrlRedirectProcessor');
