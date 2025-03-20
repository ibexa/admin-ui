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

final class Thumbnail extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SubItems\Thumbnail $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('Thumbnail');

        $generator->valueElement('uri', $data->uri);
        $generator->valueElement('mimeType', $data->mimeType);

        $generator->endObjectElement('Thumbnail');
    }
}
