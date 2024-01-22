<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeUpdateStruct;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * Base data class for ContentType update form, with FieldDefinitions data and ContentTypeDraft.
 *
 * @property \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
 */
class ContentTypeData extends ContentTypeUpdateStruct implements NewnessCheckable, TranslationContainerInterface
{
    /**
     * Trait which provides isNew(), and mandates getIdentifier().
     */
    use NewnessChecker {
        NewnessChecker::isNew as private isIdentifierNew;
    }

    /** @var \Ibexa\AdminUi\Form\Data\FieldDefinitionData[][] */
    public $fieldDefinitionsData = [];

    /** @var \Ibexa\AdminUi\Form\Data\FieldDefinitionData[][] */
    public array $metaFieldDefinitionsData = [];

    /**
     * Language Code of currently edited contentTypeDraft.
     *
     * @var string|null
     */
    public $languageCode = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft */
    protected $contentTypeDraft;

    private bool $isNew;

    /**
     * @param array<mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        $this->isNew = $this->isNew();
    }

    public function isNew(): bool
    {
        if (isset($this->isNew)) {
            return $this->isNew;
        }

        return $this->isIdentifierNew();
    }

    protected function getIdentifierValue(): string
    {
        return $this->contentTypeDraft->identifier;
    }

    /**
     * @return iterable<string, \Ibexa\AdminUi\Form\Data\FieldDefinitionData>
     */
    public function getFlatFieldDefinitionsData(): iterable
    {
        foreach ($this->fieldDefinitionsData as $outerKey => $fieldDefinitionGroupData) {
            foreach ($fieldDefinitionGroupData as $innerKey => $fieldDefinitionData) {
                yield "$outerKey.$innerKey" => $fieldDefinitionData;
            }
        }
    }

    /**
     * @return iterable<string, \Ibexa\AdminUi\Form\Data\FieldDefinitionData>
     */
    public function getFlatMetaFieldDefinitionsData(): iterable
    {
        foreach ($this->metaFieldDefinitionsData as $outerKey => $fieldDefinitionGroupData) {
            foreach ($fieldDefinitionGroupData as $innerKey => $fieldDefinitionData) {
                yield "$outerKey.$innerKey" => $fieldDefinitionData;
            }
        }
    }

    public function addFieldDefinitionData(FieldDefinitionData $fieldDefinitionData): void
    {
        $this->fieldDefinitionsData[$fieldDefinitionData->fieldGroup][$fieldDefinitionData->identifier] = $fieldDefinitionData;
    }

    public function addMetaFieldDefinitionData(FieldDefinitionData $fieldDefinitionData): void
    {
        $fieldGroup = $fieldDefinitionData->fieldGroup;
        $identifier = $fieldDefinitionData->identifier;

        $this->metaFieldDefinitionsData[$fieldGroup][$identifier] = $fieldDefinitionData;
    }

    public function replaceFieldDefinitionData(string $fieldDefinitionIdentifier, FieldDefinitionData $fieldDefinitionData): void
    {
        foreach ($this->fieldDefinitionsData as $key => $fieldDefinitionsByGroup) {
            if (isset($this->fieldDefinitionsData[$key][$fieldDefinitionIdentifier])) {
                unset($this->fieldDefinitionsData[$key][$fieldDefinitionIdentifier]);
            }
        }

        $this->fieldDefinitionsData[$fieldDefinitionData->fieldGroup][$fieldDefinitionIdentifier] = $fieldDefinitionData;
    }

    /**
     * Sort $this->fieldDefinitionsData first by position, then by identifier.
     */
    public function sortFieldDefinitions(): void
    {
        foreach ($this->fieldDefinitionsData as $key => $fieldDefinitionByGroup) {
            uasort(
                $fieldDefinitionByGroup,
                static function ($a, $b): int {
                    if ($a->fieldDefinition->position === $b->fieldDefinition->position) {
                        return $a->fieldDefinition->identifier <=> $b->fieldDefinition->identifier;
                    }

                    return $a->fieldDefinition->position <=> $b->fieldDefinition->position;
                }
            );

            $this->fieldDefinitionsData[$key] = $fieldDefinitionByGroup;
        }
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ez.content_type.descriptions', 'validators')
                ->setDesc('Content type description cannot be longer than 255 characters.'),
            Message::create('ez.content_type.identifier.pattern', 'validators')
                ->setDesc('Content type identifier may only contain letters from "a" to "z", numbers and underscores.'),
            Message::create('ez.content_type.names', 'validators')
                ->setDesc('Content type name cannot be blank and cannot be longer than 255 characters.'),
        ];
    }
}

class_alias(ContentTypeData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ContentTypeData');
