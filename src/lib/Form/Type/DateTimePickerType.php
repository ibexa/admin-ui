<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type;

use Ibexa\AdminUi\Form\DataTransformer\DateTimePickerTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimePickerType extends AbstractType
{
    public function getParent(): ?string
    {
        return IntegerType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new DateTimePickerTransformer());
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-seconds'] = (int) $options['with_seconds'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('with_seconds', true)
            ->setAllowedTypes('with_seconds', 'bool');
    }
}
