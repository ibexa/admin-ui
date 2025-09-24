<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentType;

use Ibexa\Rest\Value as RestValue;

final class FieldDefinitionDelete extends RestValue
{
    /**
     * @param string[] $fieldDefinitionIdentifiers
     */
    public function __construct(public readonly array $fieldDefinitionIdentifiers = [])
    {
    }
}
