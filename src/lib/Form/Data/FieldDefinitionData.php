<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionUpdateStruct;

/**
 * Base class for FieldDefinition forms, with corresponding FieldDefinition object.
 *
 * @property \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $fieldDefinition
 * @property \Ibexa\AdminUi\Form\Data\ContentTypeData $contentTypeData
 * @property bool $enabled
 */
class FieldDefinitionData extends FieldDefinitionUpdateStruct
{
    /**
     * @var bool
     */
    public bool $enabled;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * ContentTypeData holding current FieldDefinitionData.
     * Mainly used for validation.
     *
     * @var \Ibexa\AdminUi\Form\Data\ContentTypeData
     */
    protected $contentTypeData;

    public function getFieldTypeIdentifier()
    {
        return $this->fieldDefinition->fieldTypeIdentifier;
    }
}

class_alias(
    FieldDefinitionData::class,
    \EzSystems\RepositoryForms\Data\FieldDefinitionData::class
);

class_alias(FieldDefinitionData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\FieldDefinitionData');
