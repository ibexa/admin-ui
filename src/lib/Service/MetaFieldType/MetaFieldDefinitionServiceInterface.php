<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Service\MetaFieldType;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

interface MetaFieldDefinitionServiceInterface
{
    public function addMetaFieldDefinitions(
        ValueObject $contentType,
        ?Language $language = null
    ): void;

    public function createMetaFieldDefinitionCreateStruct(
        string $identifier,
        string $fieldGroup,
        Language $language,
        int $position
    ): FieldDefinitionCreateStruct;

    public function metaFieldDefinitionExists(
        string $fieldTypeIdentifier,
        string $fieldTypeGroup,
        ValueObject $contentType
    ): bool;

    public function getNextFieldPosition(ValueObject $contentType): int;

    public function getDefaultMetaDataFieldTypeGroup(): ?string;
}
