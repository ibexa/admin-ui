<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\UniversalDiscovery;

use Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

final class LocationDataVisitor extends AbstractLocationDataVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('LocationData');

        $this->buildLocationNode($data->getLocation(), $generator, $visitor);
        $this->buildSubItemsNode($data, $generator, $visitor);
        $this->buildVersionNode($data->getVersion(), $generator, $visitor);
        $this->buildBookmarkedNode($data->isBookmarked(), $generator);
        $this->buildPermissionNode($data->getPermissionRestrictions(), $generator);

        $generator->endObjectElement('LocationData');
    }

    private function buildSubItemsNode(
        LocationData $data,
        Generator $generator,
        Visitor $visitor
    ): void {
        $generator->startHashElement('subitems');

        $this->buildLocationsList($data, $generator, $visitor);
        $this->buildVersionList($data, $generator, $visitor);
        $this->buildTotalCountNode($data->getSubItems()['totalCount'], $generator);

        $generator->endHashElement('subitems');
    }

    private function buildLocationsList(
        LocationData $data,
        Generator $generator,
        Visitor $visitor
    ): void {
        $generator->startList('locations');

        foreach ($data->getSubItems()['locations'] as $location) {
            $generator->startHashElement('Location');
            $visitor->visitValueObject($location);
            $generator->endHashElement('Location');
        }

        $generator->endList('locations');
    }

    private function buildVersionList(
        LocationData $data,
        Generator $generator,
        Visitor $visitor
    ): void {
        if (!isset($data->getSubItems()['versions'])) {
            return;
        }

        $generator->startList('versions');

        foreach ($data->getSubItems()['versions'] as $version) {
            $generator->startHashElement('Version');
            $visitor->visitValueObject($version);
            $generator->endHashElement('Version');
        }

        $generator->endList('versions');
    }
}
