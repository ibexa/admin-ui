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

final class Owner extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SubItems\Owner $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('Owner');

        $generator->valueElement('id', $data->id);
        $generator->valueElement('name', $data->name);

        $generator->startObjectElement('contentType');
        $visitor->visitValueObject($data->contentType);
        $generator->endObjectElement('contentType');

        $generator->startObjectElement('thumbnail');
        $visitor->visitValueObject($data->thumbnail);
        $generator->endObjectElement('thumbnail');

        $generator->endObjectElement('Owner');
    }
}
