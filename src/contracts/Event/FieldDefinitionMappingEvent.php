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

class FieldDefinitionMappingEvent extends Event
{
    /**
     * Triggered when contentTypeData is created from contentTypeDraft.
     */
    public const NAME = 'field_definition.mapping';

    /** @var \Ibexa\AdminUi\Form\Data\FieldDefinitionData */
    private $fieldDefinitionData;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null */
    private $baseLanguage;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null */
    private $targetLanguage;

    private bool $isNew;

    public function __construct(
        FieldDefinitionData $fieldDefinitionData,
        ?Language $baseLanguage,
        ?Language $targetLanguage,
        bool $isNew
    ) {
        $this->baseLanguage = $baseLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->fieldDefinitionData = $fieldDefinitionData;
        $this->isNew = $isNew;
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

    public function isNew(): bool
    {
        return $this->isNew;
    }
}

class_alias(FieldDefinitionMappingEvent::class, 'EzSystems\EzPlatformAdminUi\Event\FieldDefinitionMappingEvent');
