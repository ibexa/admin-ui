<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition;

final class GetContentTreeChildrenTest extends BaseAdminUiRestWebTestCase
{
    private const ENDPOINT_CONTENT_TREE_CHILDREN_URL = 'api/ibexa/v2/location/tree/load-subitems/1/10/0';
    private const RESOURCE_TYPE = 'ContentTreeNode';
    private const SNAPSHOT_FILTER_PATH = self::RESOURCE_TYPE . '/filter/%s/%s';
    private const SNAPSHOT_NO_FILTER = 'no-filter';
    private const LOCATION_ID_MEDIA = 43;

    protected static function getEndpointsToTest(): iterable
    {
        yield from self::generateRequestDefinitionsWithAllFormats(
            (new EndpointRequestDefinition(
                'GET',
                self::ENDPOINT_CONTENT_TREE_CHILDREN_URL,
                self::RESOURCE_TYPE,
                null
            ))->withSnapshotName(self::SNAPSHOT_FILTER_PATH),
            self::SNAPSHOT_NO_FILTER
        );

        foreach (self::getFilterQuery() as $snapshotName => $filterQuery) {
            yield (
                new EndpointRequestDefinition(
                    'GET',
                    self::ENDPOINT_CONTENT_TREE_CHILDREN_URL . '?' . $filterQuery,
                    self::RESOURCE_TYPE,
                    self::generateMediaTypeString(self::RESOURCE_TYPE, 'json')
                )
            )->withSnapshotName(
                sprintf(
                    self::SNAPSHOT_FILTER_PATH,
                    '/json/',
                    $snapshotName
                )
            );
        }
    }

    /**
     * @return iterable<\Ibexa\Contracts\Test\Rest\Request\Value\EndpointRequestDefinition>
     */
    private static function generateRequestDefinitionsWithAllFormats(
        EndpointRequestDefinition $endpointRequestDefinition,
        string $snapshotName
    ): iterable {
        foreach (self::REQUIRED_FORMATS as $format) {
            $resourceType = $endpointRequestDefinition->getExpectedResourceType();
            self::assertNotNull($resourceType, "Expected resource type for $endpointRequestDefinition cannot be null");
            yield $endpointRequestDefinition
                ->withAcceptHeader(
                    self::generateMediaTypeString($resourceType, $format)
                )
                ->withSnapshotName(
                    sprintf(
                        self::SNAPSHOT_FILTER_PATH,
                        $format,
                        $snapshotName
                    )
                );
        }
    }

    /**
     * @return iterable<string, string>
     */
    private static function getFilterQuery(): iterable
    {
        foreach (self::getFilterQueryStructure() as $filterSnapshotName => $filter) {
            yield "$filterSnapshotName" => urldecode(
                http_build_query($filter)
            );
        }
    }

    /**
     * @return iterable<string, array{filter?: array<mixed>}>
     */
    private static function getFilterQueryStructure(): iterable
    {
        yield self::SNAPSHOT_NO_FILTER => [];

        yield 'filter-by-content-type-identifier-folder' => [
            'filter' => [
                'ContentTypeIdentifierCriterion' => 'folder',
            ],
        ];

        yield 'filter-by-content-type-identifier-folder-and-landing-page' => [
            'filter' => [
                'ContentTypeIdentifierCriterion' => [
                    'folder', 'landing_page',
                ],
            ],
        ];

        yield 'filter-by-content-type-identifier-folder-and-media-location-id' => [
            'filter' => [
                'AND' => [
                    'ContentTypeIdentifierCriterion' => 'folder',
                    'LocationIdCriterion' => self::LOCATION_ID_MEDIA,
                ],
            ],
        ];

        yield 'filter-by-content-type-identifier-folder-or-subtree' => [
            'filter' => [
                'OR' => [
                    'ContentTypeIdentifierCriterion' => 'folder',
                    'SubtreeCriterion' => '/1/5/',
                ],
            ],
        ];
    }
}
