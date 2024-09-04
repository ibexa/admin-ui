<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\EventListener;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Form\FormEvent;

class BuildPathFromRootListener
{
    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\URLAliasService */
    private $urlAliasService;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        LocationService $locationService,
        URLAliasService $urlAliasService,
        ConfigResolverInterface $configResolver
    ) {
        $this->locationService = $locationService;
        $this->urlAliasService = $urlAliasService;
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function onPreSubmitData(FormEvent $event): void
    {
        $data = $event->getData();

        if (empty($data['site_root'])) {
            $location = $this->locationService->loadLocation((int)$data['location']);
            if (1 >= $location->depth) {
                return;
            }
            $data['path'] = $this->createPathBasedOnParentLocation($location->parentLocationId, $data['path']);
            $event->setData($data);
        } elseif (!empty($data['site_access'])) {
            $parameterName = 'content.tree_root.location_id';
            $defaultTreeRootLocationId = $this->configResolver->getParameter($parameterName);
            $siteAccess = $data['site_access'];
            $treeRootLocationId = $this->configResolver->hasParameter($parameterName, null, $siteAccess)
                ? $this->configResolver->getParameter($parameterName, null, $siteAccess)
                : $defaultTreeRootLocationId;

            $data['path'] = $this->createPathBasedOnParentLocation((int)$treeRootLocationId, $data['path']);
            $event->setData($data);
        }
    }

    private function createPathBasedOnParentLocation(int $locationId, string $path): string
    {
        $parentLocation = $this->locationService->loadLocation($locationId);
        $urlAlias = $this->urlAliasService->reverseLookup($parentLocation);

        return $urlAlias->path . '/' . $path;
    }
}

class_alias(BuildPathFromRootListener::class, 'EzSystems\EzPlatformAdminUi\Form\EventListener\BuildPathFromRootListener');
