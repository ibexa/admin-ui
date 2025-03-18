<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type;

use Ibexa\AdminUi\Form\DataTransformer\DateIntervalToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class DateTimeIntervalType extends AbstractType
{
    public function getParent(): ?string
    {
        return FormType::class;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'datetimeinterval';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addViewTransformer(new DateIntervalToArrayTransformer())
            ->add('year', IntegerType::class)
            ->add('month', IntegerType::class)
            ->add('day', IntegerType::class)
            ->add('hour', IntegerType::class)
            ->add('minute', IntegerType::class)
            ->add('second', IntegerType::class);
    }
}
