<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Depth;
use Ibexa\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LocationPathQueryType extends OptionsResolverBasedQueryType
{
    public static function getName(): string
    {
        return 'IbexaAdminUi:LocationPath';
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setDefined('rootLocationId')
            ->setRequired('location')
            ->setAllowedTypes('location', Location::class)
            ->setAllowedTypes('rootLocationId', ['int', 'null'])
        ;
    }

    protected function doGetQuery(array $parameters): Query
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $parameters['location'];
        /** @var int $rootLocationId */
        $rootLocationId = $parameters['rootLocationId'];

        $filter = $location->id === $rootLocationId
            ? new Query\Criterion\ParentLocationId($rootLocationId)
            : new Query\Criterion\LocationId($this->getParentLocationPath($location));

        return new LocationQuery([
            'filter' => $filter,
            'sortClauses' => [new Depth()],
        ]);
    }

    private function getParentLocationPath(Location $location): array
    {
        $parentPath = array_slice($location->path, 0, -1);

        return array_map('intval', $parentPath);
    }
}

class_alias(LocationPathQueryType::class, 'EzSystems\EzPlatformAdminUi\QueryType\LocationPathQueryType');
