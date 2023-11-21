<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\UniversalDiscovery;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * @phpstan-import-type Columns from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\AccordionData
 * @phpstan-import-type Column from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\AccordionData
 * @phpstan-import-type SubItems from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\AccordionData
 */
final class AccordionDataVisitor extends AbstractLocationDataVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\UniversalDiscovery\AccordionData $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('AccordionData');

        $this->buildBreadcrumbNode($data->getBreadcrumb(), $generator, $visitor);
        $this->buildColumnsNode($data->getColumns(), $generator, $visitor);

        $generator->endObjectElement('AccordionData');
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Location> $breadcrumb
     */
    private function buildBreadcrumbNode(
        array $breadcrumb,
        Generator $generator,
        Visitor $visitor
    ): void {
        $generator->startList('breadcrumb');

        foreach ($breadcrumb as $location) {
            $generator->startObjectElement('Location');
            $visitor->visitValueObject($location);
            $generator->endObjectElement('Location');
        }

        $generator->endList('breadcrumb');
    }

    /**
     * @phpstan-param Columns $columns
     */
    private function buildColumnsNode(
        array $columns,
        Generator $generator,
        Visitor $visitor
    ): void {
        $generator->startHashElement('columns');

        foreach ($columns as $locationId => $column) {
            $this->buildColumnNode($locationId, $column, $generator, $visitor);
        }

        $generator->endHashElement('columns');
    }

    /**
     * @phpstan-param Column $column
     */
    private function buildColumnNode(
        int $locationId,
        array $column,
        Generator $generator,
        Visitor $visitor
    ): void {
        $name = (string)$locationId;
        $generator->startHashElement($name);

        $this->buildBookmarkedNode($column['bookmarked'] ?? null, $generator);
        $this->buildColumnLocationNode($column['location'], $generator, $visitor);
        $this->buildPermissionNode($column['permissions'] ?? null, $generator);
        $this->buildSubItemsNode($column['subitems'], $generator, $visitor);
        $this->buildVersionNode($column['version'] ?? null, $generator, $visitor);

        $generator->endHashElement($name);
    }

    private function buildColumnLocationNode(
        ?Location $location,
        Generator $generator,
        Visitor $visitor
    ): void {
        if (null === $location) {
            $generator->generateFieldTypeHash('location', null);
        } else {
            $this->buildLocationNode($location, $generator, $visitor);
        }
    }

    /**
     * @phpstan-param SubItems $subitems
     */
    private function buildSubItemsNode(
        array $subitems,
        Generator $generator,
        Visitor $visitor
    ): void {
        $generator->startHashElement('subitems');

        $this->buildLocationListNode($subitems['locations'], $generator, $visitor);
        $this->buildTotalCountNode($subitems['totalCount'], $generator);

        $generator->endHashElement('subitems');
    }

    /**
     * @param array<\Ibexa\Rest\Server\Values\RestLocation> $locations
     */
    private function buildLocationListNode(
        array $locations,
        Generator $generator,
        Visitor $visitor
    ): void {
        $generator->startList('locations');

        foreach ($locations as $location) {
            $generator->startHashElement('Location');
            $visitor->visitValueObject($location);
            $generator->endHashElement('Location');
        }

        $generator->endList('locations');
    }
}
