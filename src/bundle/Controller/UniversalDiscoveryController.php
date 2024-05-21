<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\REST\Value\UniversalDiscovery\AccordionData;
use Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData;
use Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationListData;
use Ibexa\AdminUi\REST\Value\UniversalDiscovery\RequestQuery;
use Ibexa\Contracts\AdminUi\UniversalDiscovery\Provider;
use Ibexa\Rest\Server\Controller;
use Symfony\Component\HttpFoundation\Request;

class UniversalDiscoveryController extends Controller
{
    /** @var \Ibexa\Contracts\AdminUi\UniversalDiscovery\Provider */
    private $provider;

    public function __construct(
        Provider $provider
    ) {
        $this->provider = $provider;
    }

    public function locationsAction(Request $request): LocationListData
    {
        return new LocationListData(
            $this->provider->getLocations(
                explode(
                    ',',
                    $request->query->get('locationIds', '')
                )
            )
        );
    }

    public function locationAction(RequestQuery $requestQuery): LocationData
    {
        $data = $this->provider->getLocationData(
            $requestQuery->getLocationId(),
            $requestQuery->getOffset(),
            $requestQuery->getLimit(),
            $requestQuery->getSortClause()
        );

        return new LocationData(
            $data['subitems'],
            $data['location'] ?? null,
            $data['bookmarked'] ?? null,
            $data['permissions'] ?? null,
            $data['version'] ?? null,
        );
    }

    public function locationGridViewAction(RequestQuery $requestQuery): LocationData
    {
        $data = $this->provider->getLocationGridViewData(
            $requestQuery->getLocationId(),
            $requestQuery->getOffset(),
            $requestQuery->getLimit(),
            $requestQuery->getSortClause()
        );

        return new LocationData(
            $data['subitems'],
            $data['location'] ?? null,
            $data['bookmarked'] ?? null,
            $data['permissions'] ?? null,
            $data['version'] ?? null,
        );
    }

    public function accordionAction(RequestQuery $requestQuery): AccordionData
    {
        $locationId = $requestQuery->getLocationId();
        $rootLocationId = $requestQuery->getRootLocationId();

        $breadcrumbLocations = $locationId !== $rootLocationId
            ? $this->provider->getBreadcrumbLocations($locationId, $rootLocationId)
            : [];

        $columns = $this->provider->getColumns(
            $requestQuery->getLocationId(),
            $requestQuery->getLimit(),
            $requestQuery->getSortClause(),
            false,
            $rootLocationId
        );

        return new AccordionData(
            $breadcrumbLocations,
            $columns
        );
    }

    public function accordionGridViewAction(RequestQuery $requestQuery): AccordionData
    {
        $locationId = $requestQuery->getLocationId();
        $rootLocationId = $requestQuery->getRootLocationId();

        $columns = $this->provider->getColumns(
            $locationId,
            $requestQuery->getLimit(),
            $requestQuery->getSortClause(),
            true,
            $rootLocationId
        );

        return new AccordionData(
            $this->provider->getBreadcrumbLocations($locationId),
            $columns
        );
    }
}
