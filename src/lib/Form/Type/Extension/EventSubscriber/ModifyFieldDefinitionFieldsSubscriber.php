<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Extension\EventSubscriber;

use Ibexa\AdminUi\Form\Type\FieldDefinition\FieldDefinitionType;
use Ibexa\Contracts\Core\Specification\SpecificationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Modifies CT editing form by rebuilding field definition list with custom options on given field type.
 */
final class ModifyFieldDefinitionFieldsSubscriber implements EventSubscriberInterface
{
    private string $fieldTypeIdentifier;

    /** @var string[] */
    private array $fieldIdentifiers;

    /** @var array<string, mixed> */
    private array $modifiedOptions;

    private ?SpecificationInterface $contentTypeSpecification;

    /**
     * @param string[]|string $fieldIdentifiers
     * @param array<string, mixed> $modifiedOptions
     */
    public function __construct(
        string $fieldTypeIdentifier,
        array $modifiedOptions,
        $fieldIdentifiers = [],
        ?SpecificationInterface $contentTypeSpecification = null
    ) {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->modifiedOptions = $modifiedOptions;
        $this->fieldIdentifiers = is_array($fieldIdentifiers) ? $fieldIdentifiers : [$fieldIdentifiers];
        $this->contentTypeSpecification = $contentTypeSpecification;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData'],
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var array<string, \Ibexa\AdminUi\Form\Data\FieldDefinitionData>|null $data */
        $data = $event->getData();
        $form = $event->getForm();

        if (empty($data)) {
            return;
        }

        $firstField = reset($data);
        $contentTypeDraft = $firstField->contentTypeData->contentTypeDraft ?? null;

        if (
            $this->contentTypeSpecification !== null &&
            !$this->contentTypeSpecification->isSatisfiedBy($contentTypeDraft)
        ) {
            return;
        }
        foreach ($data as $fieldIdentifier => $fieldTypeData) {
            $matchesType = $this->fieldTypeIdentifier === $fieldTypeData->fieldDefinition->fieldTypeIdentifier;
            $matchesId = in_array($fieldIdentifier, $this->fieldIdentifiers, true);

            if (!($matchesType || $matchesId)) {
                continue;
            }

            if (!$form->has($fieldIdentifier)) {
                continue;
            }

            $baseFieldForm = $form->get($fieldIdentifier);
            $baseFieldFormName = $baseFieldForm->getName();

            $options = array_merge(
                $baseFieldForm->getConfig()->getOptions(),
                $this->modifiedOptions
            );

            $form->remove($baseFieldFormName);
            $form->add($baseFieldFormName, FieldDefinitionType::class, $options);
        }
    }
}
