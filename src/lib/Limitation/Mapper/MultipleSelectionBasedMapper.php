<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationFormMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;

/**
 * Base class for mappers based on multiple selection.
 */
abstract class MultipleSelectionBasedMapper implements LimitationFormMapperInterface
{
    private string $template;

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    public function mapLimitationForm(FormInterface $form, Limitation $data): void
    {
        $options = $this->getChoiceFieldOptions() + [
            'multiple' => true,
            'label' => LimitationIdentifierToLabelConverter::convert($data->getIdentifier()),
            'required' => false,
        ];
        $choices = $this->getSelectionChoices();
        asort($choices, SORT_NATURAL | SORT_FLAG_CASE);
        $options['choices'] = array_flip($choices);
        $form->add('limitationValues', ChoiceType::class, $options);
    }

    /**
     * Returns value choices to display, as expected by the "choices" option from Choice field.
     *
     * @return array<int, string>
     */
    abstract protected function getSelectionChoices(): array;

    /**
     * Returns custom options.
     *
     * @return array<mixed>
     */
    protected function getChoiceFieldOptions(): array
    {
        return [];
    }

    public function setFormTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getFormTemplate(): string
    {
        return $this->template;
    }

    public function filterLimitationValues(Limitation $limitation): void
    {
    }
}
