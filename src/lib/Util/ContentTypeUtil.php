<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

final readonly class ContentTypeUtil
{
    public function hasFieldType(ContentType $contentType, string $fieldTypeIdentifier): bool
    {
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->getFieldTypeIdentifier() === $fieldTypeIdentifier) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition[]
     */
    public function findFieldDefinitions(ContentType $contentType, string $fieldTypeIdentifier): array
    {
        $fieldTypes = [];
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->getFieldTypeIdentifier() === $fieldTypeIdentifier) {
                $fieldTypes[] = $fieldDefinition;
            }
        }

        return $fieldTypes;
    }
}
