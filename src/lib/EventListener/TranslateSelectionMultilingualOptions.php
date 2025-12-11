<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Contracts\AdminUi\Event\FieldDefinitionMappingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class TranslateSelectionMultilingualOptions implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FieldDefinitionMappingEvent::NAME => ['setMultilingualOptions', 30]];
    }

    public function setMultilingualOptions(FieldDefinitionMappingEvent $event): void
    {
        $fieldDefinition = $event->getFieldDefinitionData()->fieldDefinition;
        if ('ibexa_selection' !== $fieldDefinition->getFieldTypeIdentifier()) {
            return;
        }

        $baseLanguage = $event->getBaseLanguage();
        $targetLanguage = $event->getTargetLanguage();

        if (null === $baseLanguage || null === $targetLanguage) {
            return;
        }

        $fieldDefinitionData = $event->getFieldDefinitionData();
        $fieldSettings = $fieldDefinitionData->fieldSettings;

        if (isset($fieldSettings['multilingualOptions'][$baseLanguage->getLanguageCode()])) {
            $fieldSettings['multilingualOptions'][$targetLanguage->getLanguageCode()] = $fieldSettings['multilingualOptions'][$baseLanguage->getLanguageCode()];
        }

        $fieldDefinitionData->fieldSettings = $fieldSettings;

        $event->setFieldDefinitionData($fieldDefinitionData);
    }
}
