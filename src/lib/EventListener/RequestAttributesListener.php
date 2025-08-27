<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Bundle\AdminUi\IbexaAdminUiBundle;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Collects parameters for the ViewBuilder from the Request.
 */
final readonly class RequestAttributesListener implements EventSubscriberInterface
{
    private const string TRANSLATED_CONTENT_VIEW_ROUTE_NAME = 'ibexa.content.translation.view';

    /**
     * @param string[][] $siteAccessGroups
     */
    public function __construct(
        private array $siteAccessGroups,
        private Repository $repository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'addRequestAttributes'];
    }

    /**
     * Adds all the request attributes to the parameters.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function addRequestAttributes(FilterViewBuilderParametersEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isAdmin($request)) {
            return;
        }

        $parameterBag = $event->getParameters();

        if ($parameterBag->has('locationId') && null !== $parameterBag->get('locationId')) {
            $location = $this->loadLocation((int)$parameterBag->get('locationId'));
            $parameterBag->set('location', $location);
        }

        if ($this->hasContentLanguage($request, $parameterBag)) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $parameterBag->get('location');

            $languageCode = $parameterBag->get('languageCode');

            $content = $this->loadContent($location->getContentInfo()->getId(), $languageCode);
            $parameterBag->set('content', $content);
        }
    }

    private function hasContentLanguage(Request $request, ParameterBag $parameterBag): bool
    {
        return $parameterBag->has('languageCode')
            && $parameterBag->has('location')
            && $request->get('_route') === self::TRANSLATED_CONTENT_VIEW_ROUTE_NAME;
    }

    private function loadLocation(int $locationId): Location
    {
        return $this->repository->sudo(
            static function (Repository $repository) use ($locationId): Location {
                return $repository->getLocationService()->loadLocation($locationId);
            }
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadContent(int $contentId, ?string $language): Content
    {
        return $this->repository->getContentService()->loadContent(
            $contentId,
            $language ? [$language] : null
        );
    }

    private function isAdmin(Request $request): bool
    {
        $siteAccess = $request->attributes->get('siteaccess');

        return in_array(
            $siteAccess->name,
            $this->siteAccessGroups[IbexaAdminUiBundle::ADMIN_GROUP_NAME],
            true
        );
    }
}
