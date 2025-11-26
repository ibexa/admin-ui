<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\SiteAccess;

use Ibexa\AdminUi\REST\Value\SiteAccess\SiteAccessesList;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;

final class SiteAccessController extends RestController
{
    private ServiceLocator $siteAccessResolvers;

    public function __construct(
        ServiceLocator $siteAccessResolvers
    ) {
        $this->siteAccessResolvers = $siteAccessResolvers;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function loadForLocation(Request $request, Location $location): SiteAccessesList
    {
        $resolverType = $request->query->get('resolver_type', 'non_admin');

        try {
            /** @var \Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface $siteAccessResolver */
            $siteAccessResolver = $this->siteAccessResolvers->get($resolverType);
        } catch (NotFoundExceptionInterface $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new SiteAccessesList($siteAccessResolver->getSiteAccessesListForLocation($location));
    }
}
