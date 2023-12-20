<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Strategy\FocusMode;

use Ibexa\Contracts\AdminUi\FocusMode\RedirectStrategyInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Routing\RouterInterface;

final class ContentStructureRedirectStrategy implements RedirectStrategyInterface
{
    private ConfigResolverInterface $configResolver;

    private LocationService $locationService;

    private RouterInterface $router;

    public function __construct(
        ConfigResolverInterface $configResolver,
        LocationService $locationService,
        RouterInterface $router
    ) {
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
        $this->router = $router;
    }

    public function supports(string $route): bool
    {
        return $route === 'ibexa.section.list'
            || $route === 'ibexa.content_type_group.list'
            || $route === 'ibexa.object_state.groups.list'
            || $route === 'ibexa.content_type_group.view'
        ;
    }

    public function generateRedirectPath(string $originalPath): string
    {
        $locationId = $this->configResolver->getParameter('location_ids.content_structure');
        $location = $this->locationService->loadLocation($locationId);
        $contentId = $location->getContentInfo()->id;

        return $this->router->generate('ibexa.content.view', [
            'locationId' => $locationId,
            'contentId' => $contentId,
        ]);
    }
}
