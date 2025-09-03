<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\ContentTree;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Symfony\Component\HttpFoundation\Response;

class Node extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\Node $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('ContentTreeNode');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTreeNode'));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->valueElement('locationId', $data->locationId);
        $generator->valueElement('pathString', $data->pathString);
        $generator->valueElement('contentId', $data->contentId);
        $generator->valueElement('versionNo', $data->versionNo);
        $generator->valueElement('mainLanguageCode', $data->mainLanguageCode);
        $generator->valueElement('name', $data->name);
        $generator->valueElement('contentTypeIdentifier', $data->contentTypeIdentifier);
        $generator->valueElement('isContainer', $generator->serializeBool($data->isContainer));
        $generator->valueElement('isInvisible', $generator->serializeBool($data->isInvisible));
        $generator->valueElement('isHidden', $generator->serializeBool($data->isHidden));
        $generator->valueElement('displayLimit', $data->displayLimit);
        $generator->valueElement('totalChildrenCount', $data->totalChildrenCount);
        $generator->valueElement('reverseRelationsCount', $data->reverseRelationsCount);
        $generator->valueElement('isBookmarked', $generator->serializeBool($data->isBookmarked));

        $generator->startList('children');

        foreach ($data->children as $child) {
            $visitor->visitValueObject($child);
        }

        $generator->endList('children');

        $generator->endObjectElement('ContentTreeNode');
    }
}
