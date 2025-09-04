<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Extension\EventSubscriber;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Form\Type\FieldDefinition\FieldDefinitionType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Specification\SpecificationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Rebuilds specific field definitions in the Content Type editing form with custom options for a given field type and set of field identifiers.
 */
final readonly class ModifyFieldDefinitionFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<string, mixed> $modifiedOptions
     * @param array<string> $fieldIdentifiers
     */
    public function __construct(
        private string $fieldTypeIdentifier,
        private array $modifiedOptions,
        private array $fieldIdentifiers = [],
        private ?SpecificationInterface $contentTypeSpecification = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => ['onPostSetData'],
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        /** @var array<string, \Ibexa\AdminUi\Form\Data\FieldDefinitionData>|null $data */
        $data = $event->getData();
        $form = $event->getForm();

        if (empty($data)) {
            return;
        }

        if (!$this->isApplicableToContentTypeDraft($this->getContentTypeDraft($data))) {
            return;
        }

        foreach ($data as $fieldIdentifier => $fieldTypeData) {
            if (!$form->has($fieldIdentifier)) {
                continue;
            }

            if (!$this->acceptsFieldDefinition($fieldTypeData, $fieldIdentifier)) {
                continue;
            }

            $this->rebuildFieldForm($form, $fieldIdentifier);
        }
    }

    private function isApplicableToContentTypeDraft(?ContentTypeDraft $contentTypeDraft): bool
    {
        if ($this->contentTypeSpecification === null) {
            return true;
        }

        if ($contentTypeDraft === null) {
            return false;
        }

        return $this->contentTypeSpecification->isSatisfiedBy($contentTypeDraft);
    }

    /**
     * @param array<string, \Ibexa\AdminUi\Form\Data\FieldDefinitionData> $data
     */
    private function getContentTypeDraft(array $data): ?ContentTypeDraft
    {
        $firstField = reset($data);
        if ($firstField instanceof FieldDefinitionData && isset($firstField->contentTypeData)) {
            return $firstField->contentTypeData->contentTypeDraft ?? null;
        }

        return null;
    }

    private function acceptsFieldDefinition(FieldDefinitionData $field, string $identifier): bool
    {
        $matchesType = $this->fieldTypeIdentifier === $field->getFieldTypeIdentifier();
        $matchesIdentifier = in_array($identifier, $this->fieldIdentifiers, true);

        return $matchesType || $matchesIdentifier;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<\Ibexa\AdminUi\Form\Data\FieldDefinitionData[]> $form
     */
    private function rebuildFieldForm(FormInterface $form, string $name): void
    {
        $baseFieldForm = $form->get($name);
        $baseFieldFormName = $baseFieldForm->getName();

        $options = array_merge(
            $baseFieldForm->getConfig()->getOptions(),
            $this->modifiedOptions
        );

        $form->remove($baseFieldFormName);
        $form->add($baseFieldFormName, FieldDefinitionType::class, $options);
    }
}
