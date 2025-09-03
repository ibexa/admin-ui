<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Extension\EventSubscriber;

use Ibexa\AdminUi\Form\Type\FieldDefinition\FieldDefinitionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Modifies CT editing form by rebuilding field definition list with custom options on given field type.
 */
final readonly class ModifyFieldDefinitionFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<string, mixed> $modifiedOptions
     */
    public function __construct(
        private string $fieldTypeIdentifier,
        private array $modifiedOptions
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

        if (null === $data) {
            return;
        }

        foreach ($data as $fieldTypeIdentifier => $fieldTypeData) {
            if ($this->fieldTypeIdentifier !== $fieldTypeData->getFieldTypeIdentifier()) {
                continue;
            }

            if (!$form->has($fieldTypeIdentifier)) {
                return;
            }

            $baseFieldForm = $form->get($fieldTypeIdentifier);
            $baseFieldFormName = $baseFieldForm->getName();

            $form->remove($baseFieldFormName);

            $options = array_merge(
                $baseFieldForm->getConfig()->getOptions(),
                $this->modifiedOptions
            );

            $form->add($baseFieldFormName, FieldDefinitionType::class, $options);
        }
    }
}
