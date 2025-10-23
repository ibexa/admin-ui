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

final readonly class UrlRedirectProcessor implements EventSubscriberInterface
{
    /**
     * @param array<string, string[]> $siteaccessGroups
     */
    public function __construct(
        private SiteAccess $siteaccess,
        private SystemUrlRedirectProcessor $systemUrlRedirectProcessor,
        private array $siteaccessGroups
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['processRedirectAfterPublish', 0],
            ContentFormEvents::CONTENT_CANCEL => ['processRedirectAfterCancel', 0],
        ];
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function processRedirectAfterPublish(FormActionEvent $event): void
    {
        if ($event->getForm()['redirectUrlAfterPublish']?->getData()) {
            return;
        }

        if ($this->isAdminSiteaccess()) {
            return;
        }

        $this->systemUrlRedirectProcessor->processRedirectAfterPublish($event);
    }

    /**
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
     * @throws InvalidArgumentException
     */
    private function isAdminSiteaccess(): bool
    {
        return (new IsAdmin($this->siteaccessGroups))->isSatisfiedBy($this->siteaccess);
    }
}
