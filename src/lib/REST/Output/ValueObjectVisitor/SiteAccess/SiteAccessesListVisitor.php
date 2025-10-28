<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\SiteAccess;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

final class SiteAccessesListVisitor extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SiteAccess\SiteAccessesList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('SiteAccessesList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('SiteAccessesList'));

        $generator->startList('values');
        foreach ($data->getSiteAccesses() as $siteAccess) {
            $generator->startObjectElement('SiteAccess');

            $generator->startValueElement('name', $siteAccess->name);
            $generator->endValueElement('name');

            $generator->startValueElement('provider', $siteAccess->provider);
            $generator->endValueElement('provider');

            $generator->endObjectElement('SiteAccess');
        }
        $generator->endList('values');

        $generator->endObjectElement('SiteAccessesList');
    }
}
