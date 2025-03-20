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

final class SubItem extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SubItems\SubItem $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('SubItem');

        $generator->valueElement('id', $data->id);
        $generator->valueElement('remoteId', $data->remoteId);
        $generator->valueElement('hidden', $data->hidden);
        $generator->valueElement('priority', $data->priority);
        $generator->valueElement('pathString', $data->pathString);

        $generator->startObjectElement('contentThumbnail');
        $visitor->visitValueObject($data->contentThumbnail);
        $generator->endObjectElement('contentThumbnail');

        $generator->valueElement('name', $data->name);
        $generator->valueElement('contentRemoteId', $data->contentRemoteId);
        $generator->valueElement('mainLanguageCode', $data->mainLanguageCode);

        $generator->startObjectElement('owner');
        $visitor->visitValueObject($data->owner);
        $generator->endObjectElement('owner');

        $generator->valueElement('currentVersionNo', $data->currentVersionNo);

        $generator->startObjectElement('currentVersionOwner');
        $visitor->visitValueObject($data->currentVersionOwner);
        $generator->endObjectElement('currentVersionOwner');

        $generator->startObjectElement('contentType');
        $visitor->visitValueObject($data->contentType);
        $generator->endObjectElement('contentType');

        $generator->valueElement('sectionName', $data->sectionName);
        $generator->valueElement('publishedDate', $data->publishedDate);
        $generator->valueElement('modificationDate', $data->modificationDate);

        $generator->endObjectElement('SubItem');
    }
}
