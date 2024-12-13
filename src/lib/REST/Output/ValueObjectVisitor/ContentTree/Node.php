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
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\Node $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTreeNode');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTreeNode'));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->startValueElement('locationId', $data->locationId);
        $generator->endValueElement('locationId');

        $generator->startValueElement('pathString', $data->pathString);
        $generator->endValueElement('pathString');

        $generator->startValueElement('contentId', $data->contentId);
        $generator->endValueElement('contentId');

        $generator->valueElement('versionNo', $data->versionNo);

        $generator->valueElement('mainLanguageCode', $data->mainLanguageCode);

        $generator->startValueElement('name', $data->name);
        $generator->endValueElement('name');

        $generator->startValueElement('contentTypeIdentifier', $data->contentTypeIdentifier);
        $generator->endValueElement('contentTypeIdentifier');

        $generator->startValueElement('isContainer', $generator->serializeBool($data->isContainer));
        $generator->endValueElement('isContainer');

        $generator->startValueElement('isInvisible', $generator->serializeBool($data->isInvisible));
        $generator->endValueElement('isInvisible');

        $generator->startValueElement('displayLimit', $data->displayLimit);
        $generator->endValueElement('displayLimit');

        $generator->startValueElement('totalChildrenCount', $data->totalChildrenCount);
        $generator->endValueElement('totalChildrenCount');

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
