<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\UniversalDiscovery;

use Ibexa\Rest\Value;

/**
 * @phpstan-import-type SubItems from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData
 * @phpstan-import-type PermissionRestrictions from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData
 *
 * @phpstan-type Column array{
 *     location: \Ibexa\Contracts\Core\Repository\Values\Content\Location|null,
 *     subitems: SubItems,
 *     bookmarked?: bool,
 *     permissions?: PermissionRestrictions,
 *     version?: \Ibexa\Rest\Server\Values\Version,
 * }
 * @phpstan-type Columns array<int, Column>
 */
final class AccordionData extends Value
{
    /** @var array<\Ibexa\Contracts\Core\Repository\Values\Content\Location> */
    private array $breadcrumb;

    /** @phpstan-var Columns $columns */
    private array $columns;

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Location> $breadcrumb
     *
     * @phpstan-param Columns $columns
     */
    public function __construct(array $breadcrumb, array $columns)
    {
        $this->breadcrumb = $breadcrumb;
        $this->columns = $columns;
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\Content\Location>
     */
    public function getBreadcrumb(): array
    {
        return $this->breadcrumb;
    }

    /**
     * @phpstan-return Columns
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
