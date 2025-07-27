<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentType;

use Ibexa\Rest\Value as RestValue;

final class FieldDefinitionReorder extends RestValue
{
    /** @var string[] */
    public $fieldDefinitionIdentifiers;

    /**
     * @param  string[] $fieldDefinitionIdentifiers
     */
    public function __construct(array $fieldDefinitionIdentifiers = [])
    {
        $this->fieldDefinitionIdentifiers = $fieldDefinitionIdentifiers;
    }
}
