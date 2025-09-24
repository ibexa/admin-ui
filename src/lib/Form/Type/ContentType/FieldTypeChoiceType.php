<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\Core\FieldType\FieldTypeRegistry;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type for field type selection.
 *
 * @extends \Symfony\Component\Form\AbstractType<array<string, mixed>>
 */
final class FieldTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly FieldTypeRegistry $fieldTypeRegistry,
        private readonly TranslatorInterface $translator
    ) {
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

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * Returns a hash, with fieldType identifiers as keys and human readable names as values.
     *
     * @return array<string, string>
     */
    private function getFieldTypeChoices(): array
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
     */
    private function getFieldTypeLabel(string $fieldTypeIdentifier): string
    {
        return $this->translator->trans(/** @Ignore */
            $fieldTypeIdentifier . '.name',
            [],
            'ibexa_fieldtypes'
        );
    }
}
