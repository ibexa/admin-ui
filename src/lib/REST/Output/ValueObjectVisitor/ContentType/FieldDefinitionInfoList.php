<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\ContentType;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\RestContentTypeBase;

final class FieldDefinitionInfoList extends RestContentTypeBase
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionInfoList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $fieldDefinitionList = $data;

        $generator->startObjectElement('FieldDefinitions', 'FieldDefinitionInfoList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('FieldDefinitionInfoList'));

        $generator->startList('FieldDefinitionInfo');
        foreach ($fieldDefinitionList->fieldDefinitions as $fieldDefinition) {
            $generator->startObjectElement('FieldDefinitionInfo');

            $generator->valueElement('id', $fieldDefinition->id);
            $generator->valueElement('identifier', $fieldDefinition->identifier);
            $generator->valueElement('position', $fieldDefinition->position);

            $this->visitNamesList($generator, $fieldDefinition->getNames());

            $generator->endObjectElement('FieldDefinitionInfo');
        }
        $generator->endList('FieldDefinitionInfo');

        $generator->endObjectElement('FieldDefinitions');
    }
}
