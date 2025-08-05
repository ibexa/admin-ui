<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\UniversalDiscovery;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;

interface Provider
{
    public const int ROOT_LOCATION_ID = 1;

    public const string SORT_CLAUSE_DATE_PUBLISHED = 'DatePublished';
    public const string SORT_CLAUSE_CONTENT_NAME = 'ContentName';

    /**
     * @return array<int, mixed>
     */
    public function getColumns(
        int $locationId,
        int $limit,
        Query\SortClause $sortClause,
        bool $gridView = false,
        int $rootLocationId = self::ROOT_LOCATION_ID
    ): array;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getBreadcrumbLocations(
        int $locationId,
        int $rootLocationId = self::ROOT_LOCATION_ID
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function getLocationPermissionRestrictions(Location $location): array;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content[]
     */
    public function getSubitemContents(
        int $locationId,
        int $offset,
        int $limit,
        Query\SortClause $sortClause
    ): array;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getSubitemLocations(
        int $locationId,
        int $offset,
        int $limit,
        Query\SortClause $sortClause
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function getLocationData(
        int $locationId,
        int $offset,
        int $limit,
        Query\SortClause $sortClause
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function getLocationGridViewData(
        int $locationId,
        int $offset,
        int $limit,
        Query\SortClause $sortClause
    ): array;

    /**
     * @param list<string> $locationIds
     *
     * @return array<array{
     *     location: \Ibexa\Contracts\Core\Repository\Values\Content\Location,
     *     permissions: array{
     *       create: array{
     *         hasAccess: bool,
     *         restrictedContentTypeIds: array<int>,
     *         restrictedLanguageCodes: array<string>
     *       },
     *       edit: array{
     *         hasAccess: bool,
     *         restrictedContentTypeIds: array<int>,
     *         restrictedLanguageCodes: array<string>
     *       }
     *     }
     * }>
     */
    public function getLocations(array $locationIds): array;

    public function getSortClause(string $sortClauseName, string $sortOrder): Query\SortClause;
}
