<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentType;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Rest\Value as RestValue;

final class FieldDefinitionInfoList extends RestValue
{
    /** @var FieldDefinition[] */
    public array $fieldDefinitions;

    /**
     * @param FieldDefinition[] $fieldDefinitions
     */
    public function __construct(array $fieldDefinitions)
    {
        $this->fieldDefinitions = $fieldDefinitions;
    }
}
