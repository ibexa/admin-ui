<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\Search;

use Ibexa\AdminUi\Form\Type\Date\DateIntervalType;
use Ibexa\AdminUi\Form\Type\User\UserType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
final class SearchType extends AbstractType
{
    private TranslatorInterface $translator;

    private AbstractType $baseType;

    public function __construct(AbstractType $baseType, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->baseType = $baseType;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->baseType->buildForm($builder, $options);

        $builder
            ->remove('search_in_users');

        $builder
            ->add('creator', UserType::class)
            ->add('last_modified', DateIntervalType::class)
            ->add('created', DateIntervalType::class)
            ->add('last_modified_select', ChoiceType::class, [
                'choices' => $this->getTimePeriodChoices(),
                'required' => false,
                'placeholder' => /** @Desc("Any time") */ 'search.any_time',
                'mapped' => false,
            ])
            ->add('created_select', ChoiceType::class, [
                'choices' => $this->getTimePeriodChoices(),
                'required' => false,
                'placeholder' => /** @Desc("Any time") */ 'search.any_time',
                'mapped' => false,
            ])
        ;
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->baseType->configureOptions($resolver);

        $resolver->setDefaults([
            'error_mapping' => [
                'created' => 'created_select',
                'last_modified' => 'last_modified_select',
            ],
            'translation_domain' => 'ibexa_search',
        ]);
    }

    /**
     * Generate time periods options available to choose.
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    private function getTimePeriodChoices(): array
    {
        $choices = [];
        foreach ($this->getTimePeriodField() as $label => $value) {
            $choices[$label] = $value;
        }

        return $choices;
    }

    /**
     * Returns available time periods values.
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    private function getTimePeriodField(): array
    {
        return [
            $this->translator->trans(/** @Desc("Last week") */
                'search.last_week',
                [],
                'ibexa_search'
            ) => 'P0Y0M7D',
            $this->translator->trans(/** @Desc("Last month") */
                'search.last_month',
                [],
                'ibexa_search'
            ) => 'P0Y1M0D',
            $this->translator->trans(/** @Desc("Last year") */
                'search.last_year',
                [],
                'ibexa_search'
            ) => 'P1Y0M0D',
            $this->translator->trans(/** @Desc("Custom range") */
                'search.custom_range',
                [],
                'ibexa_search'
            ) => 'custom_range',
        ];
    }
}
