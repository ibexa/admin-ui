<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\UniversalDiscovery;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

final class LocationListDataVisitor extends AbstractLocationDataVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationListData $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('LocationList');

        foreach ($data->getLocationList() as $locationList) {
            $generator->startList('locations');

            $generator->startHashElement('locationWithPermissions');

            $this->buildLocationNode($locationList['location'], $generator, $visitor);
            $this->buildPermissionNode($locationList['permissions'], $generator);

            $generator->endHashElement('locationWithPermissions');

            $generator->endList('locations');
        }

        $generator->endObjectElement('LocationList');
    }
}
