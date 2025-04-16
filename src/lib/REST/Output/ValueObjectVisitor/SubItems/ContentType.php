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

final class ContentType extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SubItems\ContentType $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('ContentType');

        $generator->valueElement('name', $data->name);
        $generator->valueElement('identifier', $data->identifier);

        $generator->endObjectElement('ContentType');
    }
}
