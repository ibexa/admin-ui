<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\SubItems;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

final class ContentInfo extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SubItems\ContentInfo $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('ContentInfo');

        $generator->valueElement('id', $data->id);
        $generator->valueElement('remoteId', $data->remoteId);
        $generator->valueElement('mainLanguageCode', $data->mainLanguageCode);
        $generator->valueElement('name', $data->name);
        $generator->valueElement('sectionName', $data->sectionName);
        $generator->valueElement('publishedDate', $data->publishedDate);
        $generator->valueElement('modificationDate', $data->modificationDate);

        $generator->endObjectElement('ContentInfo');
    }
}
