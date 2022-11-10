<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class ContentTypeUtil
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     * @param string $fieldTypeIdentifier
     *
     * @return bool
     */
    public function hasFieldType(ContentType $contentType, string $fieldTypeIdentifier): bool
    {
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === $fieldTypeIdentifier) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     * @param string $fieldTypeIdentifier
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition[]
     */
    public function findFieldDefinitions(ContentType $contentType, string $fieldTypeIdentifier): array
    {
        $fieldTypes = [];
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === $fieldTypeIdentifier) {
                $fieldTypes[] = $fieldDefinition;
            }
        }

        return $fieldTypes;
    }
}

class_alias(ContentTypeUtil::class, 'EzSystems\EzPlatformAdminUi\Util\ContentTypeUtil');
