<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\Core\FieldType\FieldTypeRegistry;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type for field type selection.
 */
class FieldTypeChoiceType extends AbstractType
{
    /**
     * @var \Ibexa\Core\FieldType\FieldTypeRegistry
     */
    private $fieldTypeRegistry;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(FieldTypeRegistry $fieldTypeRegistry, TranslatorInterface $translator)
    {
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->translator = $translator;
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_contenttype_field_type_choice';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getFieldTypeChoices(),
        ]);
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    /**
     * Returns a hash, with fieldType identifiers as keys and human readable names as values.
     *
     * @return array
     */
    private function getFieldTypeChoices()
    {
        $choices = [];
        foreach ($this->fieldTypeRegistry->getConcreteFieldTypesIdentifiers() as $fieldTypeIdentifier) {
            $choices[$this->getFieldTypeLabel($fieldTypeIdentifier)] = $fieldTypeIdentifier;
        }

        ksort($choices, SORT_NATURAL);

        return $choices;
    }

    /**
     * Generate a human readable name for field type identifier.
     *
     * @param string $fieldTypeIdentifier
     *
     * @return string
     */
    private function getFieldTypeLabel($fieldTypeIdentifier)
    {
        return $this->translator->trans(/** @Ignore */
            $fieldTypeIdentifier . '.name',
            [],
            'ibexa_fieldtypes'
        );
    }
}
