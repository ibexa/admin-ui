<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LocationExtension extends AbstractExtension
{
    private const SORT_FIELD_TO_SORT_CLAUSE_MAP = [
        Location::SORT_FIELD_PATH => 'LocationPath',
        Location::SORT_FIELD_PUBLISHED => 'DatePublished',
        Location::SORT_FIELD_MODIFIED => 'DateModified',
        Location::SORT_FIELD_SECTION => 'SectionIdentifier',
        Location::SORT_FIELD_DEPTH => 'LocationDepth',
        Location::SORT_FIELD_PRIORITY => 'LocationPriority',
        Location::SORT_FIELD_NAME => 'ContentName',
        Location::SORT_FIELD_NODE_ID => 'LocationId',
        Location::SORT_FIELD_CONTENTOBJECT_ID => 'ContentId',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_location_sort_order_as_rest_value',
                static fn (Location $location): string => $location->sortOrder ? 'ascending' : 'descending'
            ),
            new TwigFunction(
                'ibexa_location_sort_field_as_rest_sort_clause',
                static fn (Location $location): string => self::SORT_FIELD_TO_SORT_CLAUSE_MAP[$location->sortField]
            ),
        ];
    }
}
