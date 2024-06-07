<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * Base class for FieldDefinition forms, with corresponding FieldDefinition object.
 *
 * @property \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $fieldDefinition
 * @property \Ibexa\AdminUi\Form\Data\ContentTypeData $contentTypeData
 */
class FieldDefinitionData extends FieldDefinitionUpdateStruct implements TranslationContainerInterface
{
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

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ez.field_definition.descriptions', 'validators')
                ->setDesc('Field definition description cannot be longer than 255 characters.'),
            Message::create('ez.field_definition.names', 'validators')
                ->setDesc('Field definition name cannot be blank cannot be longer than 255 characters.'),
        ];
    }
}

class_alias(
    FieldDefinitionData::class,
    \EzSystems\RepositoryForms\Data\FieldDefinitionData::class
);

class_alias(FieldDefinitionData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\FieldDefinitionData');
