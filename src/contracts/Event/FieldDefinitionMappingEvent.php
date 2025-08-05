<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Contracts\EventDispatcher\Event;

final class FieldDefinitionMappingEvent extends Event
{
    /**
     * Triggered when contentTypeData is created from contentTypeDraft.
     */
    public const string NAME = 'field_definition.mapping';

    public function __construct(
        private FieldDefinitionData $fieldDefinitionData,
        private readonly ?Language $baseLanguage,
        private readonly ?Language $targetLanguage
    ) {
    }

    public function getFieldDefinition(): FieldDefinition
    {
        return $this->fieldDefinitionData->fieldDefinition;
    }

    public function getFieldDefinitionData(): FieldDefinitionData
    {
        return $this->fieldDefinitionData;
    }

    public function setFieldDefinitionData(FieldDefinitionData $fieldDefinitionData): void
    {
        $this->fieldDefinitionData = $fieldDefinitionData;
    }

    public function getBaseLanguage(): ?Language
    {
        return $this->baseLanguage;
    }

    public function getTargetLanguage(): ?Language
    {
        return $this->targetLanguage;
    }
}
