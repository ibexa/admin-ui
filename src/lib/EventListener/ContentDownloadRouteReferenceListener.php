<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Ensures that download urls generated in ibexa/admin-ui are in the scope of admin siteaccess.
 *
 * @internal for internal use by AdminUI
 */
final readonly class ContentDownloadRouteReferenceListener implements EventSubscriberInterface
{
    public const string CONTENT_DOWNLOAD_ROUTE_NAME = 'ibexa.content.download';

    /**
     * @param array<string, string[]> $siteAccessGroups
     */
    public function __construct(private array $siteAccessGroups)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MVCEvents::ROUTE_REFERENCE_GENERATION => 'onRouteReferenceGeneration',
        ];
    }

    public function onRouteReferenceGeneration(RouteReferenceGenerationEvent $event): void
    {
        $routeReference = $event->getRouteReference();

        if ($routeReference->getRoute() != self::CONTENT_DOWNLOAD_ROUTE_NAME) {
            return;
        }

        /** @var \Ibexa\Core\MVC\Symfony\SiteAccess $siteaccess */
        $siteaccess = $event->getRequest()->attributes->get('siteaccess');
        if ($this->isAdminSiteAccess($siteaccess)) {
            $routeReference->set('siteaccess', $siteaccess->name);
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function isAdminSiteAccess(SiteAccess $siteAccess): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($siteAccess);
    }
}
