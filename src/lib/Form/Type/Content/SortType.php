<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<array{field: string, direction: int}>
 */
final class SortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('field', ChoiceType::class, [
                'choices' => $options['sort_fields'],
                'attr' => ['hidden' => true],
                'required' => true,
                'placeholder' => false,
                'empty_data' => $options['default']['field'],
            ])
            ->add('direction', IntegerType::class, [
                'attr' => ['hidden' => true],
                'required' => false,
                'empty_data' => $options['default']['direction'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('sort_fields');
        $resolver->setDefined('default');
        $resolver->setAllowedTypes('sort_fields', 'array');
        $resolver->setAllowedTypes('default', 'array');
    }
}
