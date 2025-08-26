<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Contracts\AdminUi\Event\FieldDefinitionMappingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PopulateFieldDefinitionData implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FieldDefinitionMappingEvent::NAME => ['populateFieldDefinition', 50]];
    }

    public function populateFieldDefinition(FieldDefinitionMappingEvent $event): void
    {
        $fieldDefinition = $event->getFieldDefinition();
        $fieldDefinitionData = $event->getFieldDefinitionData();

        $fieldDefinitionData->identifier = $fieldDefinition->getIdentifier();
        $fieldDefinitionData->names = $fieldDefinition->getNames();
        $fieldDefinitionData->descriptions = $fieldDefinition->getDescriptions();
        $fieldDefinitionData->fieldGroup = $fieldDefinition->getFieldGroup();
        $fieldDefinitionData->position = $fieldDefinition->getPosition();
        $fieldDefinitionData->isTranslatable = $fieldDefinition->isTranslatable();
        $fieldDefinitionData->isRequired = $fieldDefinition->isRequired();
        $fieldDefinitionData->isThumbnail = $fieldDefinition->isThumbnail();
        $fieldDefinitionData->isInfoCollector = $fieldDefinition->isInfoCollector();
        $fieldDefinitionData->validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        $fieldDefinitionData->fieldSettings = $fieldDefinition->getFieldSettings();
        $fieldDefinitionData->defaultValue = $fieldDefinition->getDefaultValue();
        $fieldDefinitionData->isSearchable = $fieldDefinition->isSearchable();

        $event->setFieldDefinitionData($fieldDefinitionData);
    }
}
